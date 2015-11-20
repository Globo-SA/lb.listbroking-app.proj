<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use ListBroking\AppBundle\Engine\Filter\ContactFilter\BasicContactFilter;
use ListBroking\AppBundle\Engine\Filter\ContactFilter\RequiredContactFilter;
use ListBroking\AppBundle\Engine\Filter\ContactFilterInterface;
use ListBroking\AppBundle\Engine\Filter\LeadFilter\BasicLeadFilter;
use ListBroking\AppBundle\Engine\Filter\LeadFilterInterface;
use ListBroking\AppBundle\Engine\Filter\LockFilter\CampaignLockFilter;
use ListBroking\AppBundle\Engine\Filter\LockFilter\CategoryLockFilter;
use ListBroking\AppBundle\Engine\Filter\LockFilter\ClientLockFilter;
use ListBroking\AppBundle\Engine\Filter\LockFilter\NoLocksLockFilter;
use ListBroking\AppBundle\Engine\Filter\LockFilter\ReservedLockFilter;
use ListBroking\AppBundle\Engine\Filter\LockFilter\SubCategoryLockFilter;
use ListBroking\AppBundle\Engine\Filter\LockFilterInterface;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\Lock;
use ListBroking\AppBundle\Exception\InvalidFilterObjectException;
use ListBroking\AppBundle\Form\FiltersType;

class FilterEngine
{

    /**
     * @var LockFilterInterface[]
     */
    private $lock_filter_types;

    /**
     * @var ContactFilterInterface[]
     */
    private $contact_filter_types;

    /**
     * @var LeadFilterInterface[]
     */
    private $lead_filter_types;

    public function __construct (EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;

        $this->lock_filter_types = array(
            LockFilterInterface::NO_LOCKS_TYPE          => new NoLocksLockFilter(Lock::TYPE_NO_LOCKS),
            LockFilterInterface::RESERVED_LOCK_TYPE     => new ReservedLockFilter(Lock::TYPE_RESERVED),
            LockFilterInterface::CLIENT_LOCK_TYPE       => new ClientLockFilter(Lock::TYPE_CLIENT),
            LockFilterInterface::CAMPAIGN_LOCK_TYPE     => new CampaignLockFilter(Lock::TYPE_CAMPAIGN, Lock::TYPE_CLIENT),
            LockFilterInterface::CATEGORY_LOCK_TYPE     => new CategoryLockFilter(Lock::TYPE_CATEGORY),
            LockFilterInterface::SUB_CATEGORY_LOCK_TYPE => new SubCategoryLockFilter(Lock::TYPE_SUB_CATEGORY, Lock::TYPE_CATEGORY)
        );

        $this->contact_filter_types = array(
            ContactFilterInterface::BASIC_TYPE    => new BasicContactFilter(),
            ContactFilterInterface::REQUIRED_TYPE => new RequiredContactFilter(),
        );

        $this->lead_filter_types = array(
            LeadFilterInterface::BASIC_TYPE => new BasicLeadFilter()
        );
    }

    /**
     * Compiles the filters into a runnable QueryBuilder Object
     *
     * @param Extraction $extraction
     *
     * @throws InvalidFilterObjectException
     * @return QueryBuilder
     */
    public function compileFilters (Extraction $extraction)
    {

        /**
         * This system may seem a bit complex at first sight, but
         * the main idea is to filter:
         *  . Leads by it's Phone, OppositionList and Locks (the Lead availability)
         *  . Contacts by it's demographic and location information
         * The QueryBuilder is amazing for dynamic SQL generation,
         * but can be daunting to understand, so good luck xD
         *                                        - Samuel Castro
         */

        $filters = FiltersType::prepareFilters($extraction->getFilters());

        $limit = $extraction->getQuantity();

        $lead_qb = $this->em->createQueryBuilder()
                            ->select('leads.id as lead_id, contacts.id as contact_id')
                            ->from('ListBrokingAppBundle:Lead', 'leads')
        ;

        // Check if there are Contact Deduplications
        if ( $extraction->getDeduplicationType() )
        {

            $dedup_and = $lead_qb->expr()
                                 ->andX()
                                 ->add($lead_qb->expr()
                                               ->eq('dedup.phone', 'leads.phone'))
                                 ->add($lead_qb->expr()
                                               ->eq('dedup.extraction', ":extraction"))
            ;
            $lead_qb->setParameter('extraction', $extraction);
            $lead_qb->leftJoin('ListBroking\AppBundle\Entity\ExtractionDeduplication', 'dedup', 'WITH', $dedup_and);

            // Remove ExtractionDeduplications
            if ( $extraction->getDeduplicationType() == Extraction::EXCLUDE_DEDUPLICATION_TYPE )
            {
                $lead_qb->andWhere($lead_qb->expr()
                                           ->isNull('dedup.id'));
            }

            // Include ExtractionDeduplications
            if ( $extraction->getDeduplicationType() == Extraction::INCLUDE_DEDUPLICATION_TYPE )
            {
                $lead_qb->andWhere($lead_qb->expr()
                                           ->isNotNull('dedup.id'));
            }
        }

        // Check if there are Lock filters
        if ( array_key_exists('lock', $filters) && ! empty($filters['lock']) )
        {
            $locksAndX = $lead_qb->expr()
                                 ->andX()
            ;

            // Iterate over Lock Filter Types
            foreach ( $filters['lock'] as $type => $lock_filters )
            {
                /** @var LockFilterInterface $lock_filter_type */
                $lock_filter_type = $this->lock_filter_types[$type];
                $lock_filter_type->addFilter($locksAndX, $lead_qb, $lock_filters);
            }

            // LEFT OUTER JOIN
            $lead_qb->leftJoin('leads.locks', 'locks', 'WITH', $locksAndX);
            $lead_qb->andWhere('locks.lead IS NULL');
        }

        // Check if there are Contact filters
        if ( array_key_exists('contact', $filters) && ! empty($filters['contact']) )
        {
            $contactsAndX = $lead_qb->expr()
                                    ->andX()
            ;

            // Iterate over Contact Filter Types
            foreach ( $filters['contact'] as $type => $contact_filters )
            {
                /** @var ContactFilterInterface $contact_filter_type */
                $contact_filter_type = $this->contact_filter_types[$type];
                $contact_filter_type->addFilter($contactsAndX, $lead_qb, $contact_filters);
            }

            if ( $contactsAndX->getParts() )
            {
                $lead_qb->join('leads.contacts', 'contacts', 'WITH', $contactsAndX);
            };
        }
        else
        {
            $lead_qb->join('leads.contacts', 'contacts');
        }

        // Check if there are Lead filters
        if ( array_key_exists('lead', $filters) && ! empty($filters['lead']) )
        {
            $leadsAndX = $lead_qb->expr()
                                 ->andX()
            ;

            // Iterate over Lead Filter Types
            foreach ( $filters['lead'] as $type => $lead_filters )
            {
                /** @var leadFilterInterface $lead_filter_type */
                $lead_filter_type = $this->lead_filter_types[$type];
                $lead_filter_type->addFilter($leadsAndX, $lead_qb, $lead_filters);
            }
            $lead_qb->andWhere($leadsAndX);
        }

        // Only use contacts that are ready for being used
        $lead_qb->andWhere('leads.is_ready_to_use = :is_ready_to_use')
                ->setParameter('is_ready_to_use', 1)
        ;

        // Group by Lead to get only
        // one contact per lead
        $lead_qb->groupBy('leads.id');
        $lead_qb->setMaxResults($limit);

        return $lead_qb;
    }
}