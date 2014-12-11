<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\Lock;
use ListBroking\AppBundle\Exception\InvalidFilterObjectException;

// Interfaces
use ListBroking\AppBundle\Engine\Filter\ContactFilterInterface;
use ListBroking\AppBundle\Engine\Filter\LeadFilterInterface;
use ListBroking\AppBundle\Engine\Filter\LockFilterInterface;

// Contact Filters
use ListBroking\AppBundle\Engine\Filter\ContactFilter\BasicContactFilter;

// Lead Filters
use ListBroking\AppBundle\Engine\Filter\LeadFilter\BasicLeadFilter;

// Lock Filters
use ListBroking\AppBundle\Engine\Filter\LockFilter\CampaignLockFilter;
use ListBroking\AppBundle\Engine\Filter\LockFilter\CategoryLockFilter;
use ListBroking\AppBundle\Engine\Filter\LockFilter\ClientLockFilter;
use ListBroking\AppBundle\Engine\Filter\LockFilter\NoLocksLockFilter;
use ListBroking\AppBundle\Engine\Filter\LockFilter\ReservedLockFilter;
use ListBroking\AppBundle\Engine\Filter\LockFilter\SubCategoryLockFilter;


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


    function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;

        $this->lock_filter_types = array(
            'no_locks_lock_filter' => new NoLocksLockFilter(Lock::TYPE_NO_LOCKS),
            'reserved_lock_filter' => new ReservedLockFilter(Lock::TYPE_RESERVED),
            'client_lock_filter' => new ClientLockFilter(Lock::TYPE_CLIENT),
            'campaign_lock_filter' => new CampaignLockFilter(Lock::TYPE_CAMPAIGN, Lock::TYPE_CLIENT),
            'category_lock_filter' => new CategoryLockFilter(Lock::TYPE_CATEGORY),
            'sub_category_lock_filter' => new SubCategoryLockFilter(Lock::TYPE_SUB_CATEGORY, Lock::TYPE_CATEGORY)
        );
        $this->contact_filter_types = array(
            1 => new BasicContactFilter()
        );
        $this->lead_filter_types = array(
            1 => new BasicLeadFilter()
        );
    }

    /**
     * Compiles the filters into a runnable QueryBuilder Object
     * @param Extraction $extraction
     * @throws InvalidFilterObjectException
     * @internal param array $filters
     * @internal param $limit
     * @internal param $lock_filters
     * @internal param $contact_filters
     * @return QueryBuilder
     */
    public function compileFilters(Extraction $extraction){

        /**
         * This system may seem a bit complex at first sight, but
         * the main idea is to filter:
         *  . Leads by it's Phone and Locks (the Lead availability)
         *  . Contacts by it's demographic and location information
         *
         * The QueryBuilder is amazing for dynamic SQL generation,
         * but can be daunting to understand, so good luck xD
         *                                        - Samuel Castro
         */

        $filters = $this->prepareFilters($extraction->getFilters());
        $limit = $extraction->getQuantity();

        $lead_qb = $this->em->createQueryBuilder()
                ->select('leads.id as lead_id, contacts.id as contact_id')
                ->from('ListBrokingAppBundle:Lead', 'leads')
        ;

        // Check if there are Lock filters
        if(array_key_exists('lock_filters',$filters) && !empty($filters['lock_filters'])){
            $locksOrX = $lead_qb->expr()->orX();
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
                $lock_filter_type->addFilter($locksOrX, $lead_qb, $lock_filter['filters']);
            }

            // LEFT OUTER JOIN
            $lead_qb->leftJoin('leads.locks', 'locks', 'WITH', $locksOrX);
            $lead_qb->andWhere('locks.lead IS NULL');
        }

        // Check if there are Contact filters
        if(array_key_exists('contact_filters',$filters) && !empty($filters['contact_filters'])){

            $contactsAndX = $lead_qb->expr()->andX();
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
                $contact_filter_type->addFilter($contactsAndX, $lead_qb, $contact_filter['filters']);

            }
            $lead_qb->join('leads.contacts', 'contacts', 'WITH', $contactsAndX);
        }
        else{
            $lead_qb->join('leads.contacts', 'contacts');
        }

        // Check if there are Lead filters
        if(array_key_exists('lead_filters',$filters) && !empty($filters['lead_filters'])){
            $leadsAndX = $lead_qb->expr()->andX();
            foreach($filters['lead_filters'] as $type => $lead_filter){

                // Validate the filters array
                if (!array_key_exists('filters', $lead_filter)
                    || !is_array($lead_filter['filters'])
                )
                {
                    throw new InvalidFilterObjectException(
                        'Invalid filter, must be: array(\'type\' => \'\', \'filters\' => array()), in ' .
                        __CLASS__);
                }

                /** @var leadFilterInterface $lead_filter_type */
                $lead_filter_type = $this->lead_filter_types[$type];
                $lead_filter_type->addFilter($leadsAndX, $lead_qb, $lead_filter['filters']);
            }
            $lead_qb->andWhere($leadsAndX);
        }

        // Group by Lead to get only
        // one contact per lead
        $lead_qb->groupBy('leads.id');
        $lead_qb->setMaxResults($limit);

        return $lead_qb;
    }

    /**
     * De-serializes the Filters using developer magic
     * @param $filters
     * @return array
     */
    private function prepareFilters($filters){
        $lock_filters = array();
        $contact_filters = array();
        $lead_filters = array();
        foreach ($filters as $name => $values){

            // Remove empty filters
            if(empty($values)){
                continue;
            }

            list($type, $field) = explode(':', $name);

            if($type == 'contact'){
                // Split values
                $ranges = array();
                if(!is_array($values)){
                    $values = explode(',', $values);
                    foreach ($values as $key => $value)
                    {
                        // Check if its a range
                        if(preg_match('/-/i', $value)){
                            $ranges = explode('-', $value);
                            unset($values[$key]);
                        }
                    }
                }

                // Birthdate Widget is a bit different !!
                $tmp = array();
                if($field == 'birthdate'){
                    foreach ($values as $key => $value)
                    {
                        if(!empty($value['birthdate_range'])){
                            $tmp[] = explode('-', $value['birthdate_range']);
                        }
                    }
                    $contact_filters[1]['filters'][] = array(
                        'field' => $field,
                        'opt' => 'between',
                        'value' => $tmp

                    );
                    continue;
                }

                if(count($ranges) > 0){
                    $contact_filters[1]['filters'][] = array(
                        'field' => $field,
                        'opt' => 'between',
                        'value' => $ranges

                    );
                }

                if(count($values) > 0){
                    $contact_filters[1]['filters'][] = array(
                        'field' => $field,
                        'opt' => 'equal',
                        'value' => is_array($values) ? array_values($values) : array($values)
                    );
                }
            }
            elseif($type == 'lead'){

                // Split values
                $ranges = array();
                if(!is_array($values)){
                    $values = explode(',', $values);
                    foreach ($values as $key => $value)
                    {
                        // Check if its a range
                        if(preg_match('/-/i', $value)){
                            $ranges = explode('-', $value);
                            unset($values[$key]);
                        }
                    }
                }

                if(count($ranges) > 0){
                    $lead_filters[1]['filters'][] = array(
                        'field' => $field,
                        'opt' => 'between',
                        'value' => $ranges

                    );
                }

                if(count($values) > 0){
                    $lead_filters[1]['filters'][] = array(
                        'field' => $field,
                        'opt' => 'not_equal',
                        'value' => is_array($values) ? array_values($values) : array($values)
                    );
                }
            }
            else{
                switch ($field){
                    case 'no_locks_lock_filter':
                        $lock_filters[$field]['filters'][] = array(
                            'interval' => new \DateTime()
                        );
                        break;
                    case 'reserved_lock_filter':
                        $lock_filters[$field]['filters'][] = array(
                            'interval' => new \DateTime('- 1 week')
                        );
                        break;
                    default:
                        foreach ($values as $key => $value)
                        {
                            foreach ($value as $key2 => $value2){

                                // Remove empty filters
                                if(empty($value2)){
                                    // Unset the filter as its invalid
                                    unset($values[$key]);
                                    continue;
                                }
                                if(!is_array($value2)){
                                    // Remove invalid filters
                                    if(empty($value2)){
                                        unset($values[$key][$key2]);
                                    }else{
                                        $values[$key][$key2] = $value2;
                                    }
                                }
                            }
                        }

                        // Remove empty filters
                        if(!empty($values)){
                            $lock_filters[$field]['filters'] = $values;
                        }
                        break;
                }

            }
        }

        return array(
            'lock_filters' => $lock_filters,
            'contact_filters' => $contact_filters,
            'lead_filters' => $lead_filters
        );
    }
}