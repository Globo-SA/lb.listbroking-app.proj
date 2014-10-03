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
use ListBroking\LockBundle\Engine\LockFilterType\CampaignLockFilter;
use ListBroking\LockBundle\Engine\LockFilterType\CategoryLockFilter;
use ListBroking\LockBundle\Engine\LockFilterType\ClientLockFilter;
use ListBroking\LockBundle\Engine\LockFilterType\NoLocksLockFilter;
use ListBroking\LockBundle\Engine\LockFilterType\ReservedLockFilter;
use ListBroking\LockBundle\Engine\LockFilterType\SubCategoryLockFilter;
use ListBroking\LockBundle\Entity\Lock;
use ListBroking\LockBundle\Exception\InvalidFilterObjectException;

class LockEngine
{

    /**
     * @var LockFilterInterface[]
     */
    private $filters;

    /**
     * @var LeadRepository
     */
    private $repo;

    /**
     * Array of the currently existing Filters
     * @return array
     */
    public static function filters()
    {
        // Instantiate the filters
        /** @var FilterInterface[] $filters */
        $filters = array(
            1 => new NoLocksLockFilter(1),
            2 => new ReservedLockFilter(2),
            3 => new ClientLockFilter(3),
            4 => new CampaignLockFilter(4, 3),
            5 => new CategoryLockFilter(5),
            6 => new SubCategoryLockFilter(6, 5)
        );

        return $filters;
    }

    function __construct(LeadRepository $repo)
    {
        $this->repo = $repo;
        $this->filters = LockEngine::filters();
    }

    /**
     * Compiles the filters into a runnable QueryBuilder Object
     * @param $objs
     * @return \ESO\Doctrine\ORM\QueryBuilder
     * @throws InvalidFilterObjectException
     */
    public function compileFilters($objs){

        $qb = $this->repo->createQueryBuilder();

        $baseOrX = $qb->expr()->orX();

        // Check if there are filters
        if(!empty($objs)){

            foreach($objs as $obj)
            {
                // Validate the filters array
                if (!array_key_exists('type', $obj) || !array_key_exists('filters', $obj))
                {
                    throw new InvalidFilterObjectException('Filter object is invalid, must be: ' . json_encode(array('type' => '', 'filters' => '')));
                }

                /** @var LockFilterInterface $filter_type */
                $filter_type = $this->filters[$obj['type']];
                $filter_type->addFilter($baseOrX, $qb, $obj['filters']);
            }
        }

        // LEFT OUTER JOIN
        $qb->leftJoin($this->repo->getAlias() . '.locks', 'locks', 'WITH', $baseOrX);
        $qb->andWhere('locks.lead IS NULL');

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