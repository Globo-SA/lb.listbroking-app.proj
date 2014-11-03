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

use Doctrine\ORM\AbstractQuery;
use ListBroking\LeadBundle\Repository\ORM\ContactRepository;
use ListBroking\LeadBundle\Repository\ORM\LeadRepository;
use ListBroking\LockBundle\Engine\ContactFilter\BasicContactFilter;
use ListBroking\LockBundle\Engine\LeadFilter\BasicLeadFilter;
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
     * @var LeadFilterInterface[]
     */
    private $lead_filter_types;

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
            'no_locks_lock_filter' => new NoLocksLockFilter(1),
            'reserved_lock_filter' => new ReservedLockFilter(2),
            'client_lock_filter' => new ClientLockFilter(3),
            'campaign_lock_filter' => new CampaignLockFilter(4, 3),
            'category_lock_filter' => new CategoryLockFilter(5),
            'sub_category_lock_filter' => new SubCategoryLockFilter(6, 5)
        );
        $this->contact_filter_types = array(
            1 => new BasicContactFilter()
        );
        $this->lead_filter_types = array(
            1 => new BasicLeadFilter()
        );
    }

    public function prepareFilters($filters){
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
                if($field == 'birthdate'){
                    foreach ($values as $key => $value)
                    {
                        if(!empty($value['birthdate_range'])){
                            $contact_filters[1]['filters'][] = array(
                                'field' => $field,
                                'opt' => 'between',
                                'value' => explode('-', $value['birthdate_range'])

                            );
                        }
                    }
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
                        'opt' => $field == 'id' ? 'not_equal' : 'equal',
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

    /**
     * Compiles the filters into a runnable QueryBuilder Object
     * @param array $filters
     * @param $limit
     * @throws InvalidFilterObjectException
     * @internal param $lock_filters
     * @internal param $contact_filters
     * @return \ESO\Doctrine\ORM\QueryBuilder
     */
    public function compileFilters($filters = array(), $limit){
        $lead_qb = $this->lead_repo->createQueryBuilder();

        $lead_qb->select('contacts.id');

        // Check if there are lock filters
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
            $lead_qb->leftJoin($this->lead_repo->getAlias() . '.locks', 'locks', 'WITH', $locksOrX);

            $lead_qb->andWhere('locks.lead IS NULL');
        }

        // Check if there are contact filters
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
            $lead_qb->join($this->lead_repo->getAlias() . '.contacts', 'contacts', 'WITH', $contactsAndX);
        }
        else{
            $lead_qb->join($this->lead_repo->getAlias() . '.contacts', 'contact');

        }
        $lead_qb->groupBy($this->lead_repo->getAlias() . '.id');

        // Check if there are lead filters
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
        $lead_qb->groupBy('lead.id');
        $lead_qb->setMaxResults($limit);

        return $lead_qb;
    }
}