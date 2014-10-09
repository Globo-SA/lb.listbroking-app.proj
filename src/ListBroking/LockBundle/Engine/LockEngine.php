<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LockBundle\Engine;

use ListBroking\LeadBundle\Repository\ORM\ContactRepository;
use ListBroking\LeadBundle\Repository\ORM\LeadRepository;
use ListBroking\LockBundle\Engine\ContactFilter\BasicContactFilter;
use ListBroking\LockBundle\Engine\LockFilter\CampaignLockFilter;
use ListBroking\LockBundle\Engine\LockFilter\CategoryLockFilter;
use ListBroking\LockBundle\Engine\LockFilter\ClientLockFilter;
use ListBroking\LockBundle\Engine\LockFilter\NoLocksLockFilter;
use ListBroking\LockBundle\Engine\LockFilter\ReservedLockFilter;
use ListBroking\LockBundle\Engine\LockFilter\SubCategoryLockFilter;
use ListBroking\LockBundle\Exception\InvalidFilterObjectException;

class LockEngine
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
     * @var LeadRepository
     */
    private $lead_repo;
    /**
     * @var ContactRepository
     */
    private $contact_repo;

    function __construct(LeadRepository $lead_repo, ContactRepository $contact_repo)
    {
        $this->lead_repo = $lead_repo;
        $this->contact_repo = $contact_repo;

        $this->lock_filter_types = array(
            1 => new NoLocksLockFilter(1),
            2 => new ReservedLockFilter(2),
            3 => new ClientLockFilter(3),
            4 => new CampaignLockFilter(4, 3),
            5 => new CategoryLockFilter(5),
            6 => new SubCategoryLockFilter(6, 5)
        );
        $this->contact_filter_types = array(
            1 => new BasicContactFilter()
        );
    }

    /**
     * Compiles the filters into a runnable QueryBuilder Object
     * @param array $filters
     * @throws InvalidFilterObjectException
     * @internal param $lock_filters
     * @internal param $contact_filters
     * @return \ESO\Doctrine\ORM\QueryBuilder
     */
    public function compileFilters($filters = array()){
        $qb = $this->lead_repo->createQueryBuilder();

        // Check if there are lock filters
        if(array_key_exists('lock_filters',$filters) && !empty($filters['lock_filters'])){

            $locksOrX = $qb->expr()->orX();
            foreach($filters['lock_filters'] as $type => $lock_filter)
            {
                // Validate the filters array
                if (!array_key_exists('filters', $lock_filter)
                    || !is_array($lock_filter['filters']))
                {
                    throw new InvalidFilterObjectException(
                        'Invalid filter, must be: array(\'type\' => \'\', \'filters\' => array())), in' .
                        __CLASS__);
                }

                /** @var LockFilterInterface $lock_filter_type */
                $lock_filter_type = $this->lock_filter_types[$type];
                $lock_filter_type->addFilter($locksOrX, $qb, $lock_filter['filters']);
            }

            // LEFT OUTER JOIN
            $qb->leftJoin($this->lead_repo->getAlias() . '.locks', 'locks', 'WITH', $locksOrX);
            $qb->andWhere('locks.lead IS NULL');
        }

        // Check if there are contact filters
        if(array_key_exists('contact_filters',$filters) && !empty($filters['contact_filters'])){
            $contactsAndX = $qb->expr()->andX();
            foreach($filters['contact_filters'] as $type => $contact_filter){

                // Validate the filters array
                if (!array_key_exists('filters', $contact_filter)
                    || !is_array($contact_filter['filters'])
                )
                {
                    throw new InvalidFilterObjectException(
                        'Invalid filter, must be: array(\'type\' => \'\', \'filters\' => array()), in ' .
                        __CLASS__);
                }

                /** @var ContactFilterInterface $contact_filter_type */
                $contact_filter_type = $this->contact_filter_types[$type];
                $contact_filter_type->addFilter($contactsAndX, $qb, $contact_filter['filters']);

            }
            $qb->join($this->lead_repo->getAlias() . '.contacts', 'contacts', 'WITH', $contactsAndX);
        }
        else{
            $qb->join($this->lead_repo->getAlias() . '.contacts', 'contacts');

        }

        // Cleanup the SELECT
        foreach ($this->lead_repo->getColumnNames() as $column){
            $qb->addSelect($this->lead_repo->getAlias() . '.' . $column);
        }
        foreach ($this->contact_repo->getColumnNames() as $column){
            $qb->addSelect('contacts.' . $column);
        }
        foreach ($this->contact_repo->getAssociationNames() as $association){
            if(!in_array($association, array('lead','created_by','updated_by'))){
                $qb->leftJoin('contacts.' . $association, "{$association}_entity");
                $qb->addSelect("{$association}_entity.name as {$association}");
            }
        }

        // Group by Lead to get only
        // one contact per lead
        $qb->groupBy('lead');

        return $qb;
    }
}