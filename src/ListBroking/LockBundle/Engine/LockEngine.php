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

use ListBroking\LeadBundle\Repository\ORM\LeadRepository;
use ListBroking\LockBundle\Engine\ContactFilter\BasicContactFilter;
use ListBroking\LockBundle\Engine\LockFilter\CampaignLockFilter;
use ListBroking\LockBundle\Engine\LockFilter\CategoryLockFilter;
use ListBroking\LockBundle\Engine\LockFilter\ClientLockFilter;
use ListBroking\LockBundle\Engine\LockFilter\NoLocksLockFilter;
use ListBroking\LockBundle\Engine\LockFilter\ReservedLockFilter;
use ListBroking\LockBundle\Engine\LockFilter\SubCategoryLockFilter;
use ListBroking\LockBundle\Entity\Lock;
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
    private $repo;

    function __construct(LeadRepository $repo)
    {
        $this->repo = $repo;
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
     * @param $lock_filters
     * @param $contact_filters
     * @return \ESO\Doctrine\ORM\QueryBuilder
     * @throws InvalidFilterObjectException
     */
    public function compileFilters($lock_filters = array(), $contact_filters = array()){

        $qb = $this->repo->createQueryBuilder();


        // Check if there are lock filters
        if(!empty($lock_filters)){

            $locksOrX = $qb->expr()->orX();
            foreach($lock_filters as $lock_filter)
            {
                // Validate the filters array
                if (!array_key_exists('type', $lock_filter)
                    || !array_key_exists('filters', $lock_filter)
                    || !is_array($lock_filter['filters']))
                {
                    throw new InvalidFilterObjectException(
                        'Invalid filter, must be: array(\'type\' => \'\', \'filters\' => array())), in' .
                        __CLASS__);
                }

                /** @var LockFilterInterface $lock_filter_type */
                $lock_filter_type = $this->lock_filter_types[$lock_filter['type']];
                $lock_filter_type->addFilter($locksOrX, $qb, $lock_filter['filters']);
            }

            // LEFT OUTER JOIN
            $qb->leftJoin($this->repo->getAlias() . '.locks', 'locks', 'WITH', $locksOrX);
            $qb->andWhere('locks.lead IS NULL');
        }

        // Check if there are contact filters
        if(!empty($contact_filters)){
            $contactsAndX = $qb->expr()->andX();
            foreach($contact_filters as $contact_filter){

                // Validate the filters array
                if (!array_key_exists('type', $contact_filter)
                    || !array_key_exists('filters', $contact_filter)
                    || !is_array($contact_filter['filters'])
                )
                {
                    throw new InvalidFilterObjectException(
                        'Invalid filter, must be: array(\'type\' => \'\', \'filters\' => array()), in ' .
                        __CLASS__);
                }

                /** @var ContactFilterInterface $contact_filter_type */
                $contact_filter_type = $this->contact_filter_types[$contact_filter['type']];
                $contact_filter_type->addFilter($contactsAndX, $qb, $contact_filter['filters']);

            }
            $qb->join($this->repo->getAlias() . '.contacts', 'contacts', 'WITH', $contactsAndX);
        }

        return $qb;
    }
    /**
     * Runs the lock negotiation stage and finds
     * if the object is free or not
     * @param $locks Lock array
     * @param $lock_filters array
     * @return $this
     */
    public function run($locks, $lock_filters)
    {

    }


}