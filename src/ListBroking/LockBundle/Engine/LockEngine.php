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

use ListBroking\LockBundle\Entity\Lock;
use ListBroking\LockBundle\Repository\ORM\LockRepository;

class LockEngine
{

    /**
     * @var FilterInterface[]
     */
    private $filters;

    /**
     * @var string[]
     */
    private $lock_status;

    private $repo;

    /**
     * Array of the currently existing FilterInterface class_names
     * @return array
     */
    public static function filters()
    {
        return array(
            1 => 'ReservedFilter',
            2 => 'ClientFilter',
            3 => 'CampaignFilter',
            4 => 'CategoryFilter',
            5 => 'SubCategoryFilter'
        );
    }

    /**
     * Array of the currently existing LockStatus class_names
     * @return array
     */
    public static function lockStatus()
    {
        return array(
            1 => 'LOCK_STATUS_OPEN',
            2 => 'LOCK_STATUS_EXPIRED'
        );
    }

    function __construct(LockRepository $repo)
    {
        $this->repo = $repo;

        // Instantiates the Filter classes
        foreach (LockEngine::filters() as $key => $type)
        {
            $class = 'ListBroking\\LockBundle\\Engine\FilterType\\' . $type;
            $this->filters[$key] = new $class($key);
        }

        $this->lock_status = LockEngine::lockStatus();
    }

    public function compileFilters($lock_filters){

        $qb = $this->repo->createQueryBuilder();
        foreach($lock_filters as $filter){

            /** @var FilterInterface $filter_type */
            $filter_type = $this->filters[$filter['type']];
            $filter_type->addJoin($qb, $filter);

        }

        ladybug_dump_die($qb->getQuery()->getSQL());
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