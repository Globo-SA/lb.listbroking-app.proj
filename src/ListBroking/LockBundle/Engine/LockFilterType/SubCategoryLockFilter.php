<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LockBundle\Engine\LockFilterType;

use Doctrine\ORM\Query\Expr\Orx;
use ESO\Doctrine\ORM\QueryBuilder;
use ListBroking\LockBundle\Engine\LockFilterInterface;
use ListBroking\LockBundle\Exception\InvalidFilterObjectException;
use ListBroking\LockBundle\Exception\InvalidFilterTypeException;

class SubCategoryLockFilter implements LockFilterInterface {

    /**
     * @var int
     */
    public $type_id;

    /**
     * @var int
     */
    public $parent_id;

    public function __construct($type_id, $parent_id)
    {
        $this->type_id = $type_id;
        $this->parent_id = $parent_id;
    }

    /**
     * @param Orx $orX
     * @param QueryBuilder $qb
     * @param $filters
     * @throws InvalidFilterObjectException
     * @throws InvalidFilterTypeException
     * @return mixed
     */
    public function addFilter(Orx $orX, QueryBuilder $qb, $filters)
    {
        foreach ($filters as $key => $filter)
        {
            // Validate filter array
            if(!array_key_exists('sub_category_id', $filter) || !array_key_exists('interval', $filter)){
                throw new InvalidFilterObjectException('Invalid filter object must be:' . json_encode(array('sub_category_id' => '', 'interval' => '')));
            }
            if(!($filter['interval'] instanceof \DateTime)){
                throw new InvalidFilterTypeException('The filter interval field must be an instance of \DateTime()');
            }

            $orX->addMultiple(
                array(
                    $qb->expr()->andX(
                        'locks.expiration_date <= CURRENT_TIMESTAMP()',
                        'locks.type = :sub_category_locks_type',
                        "locks.sub_category = :sub_category_locks_sub_category_id_{$key}",
                        "(locks.expiration_date >= :sub_category_locks_filter_expiration_date_{$key})"
                    ),
                    $qb->expr()->andX(
                        'locks.type = :sub_category_locks_category_type',
                        "locks.category = :sub_category_locks_category_id_{$key}",
                        "(locks.expiration_date >= CURRENT_TIMESTAMP())"
                    ),
                )
            );
            $qb->setParameter('sub_category_locks_type', $this->type_id);
            $qb->setParameter("sub_category_locks_sub_category_id_{$key}", $filter['sub_category_id']);
            $qb->setParameter("sub_category_locks_filter_expiration_date_{$key}", $filter['interval']);

            $qb->setParameter('sub_category_locks_category_type', $this->parent_id);
            $qb->setParameter("sub_category_locks_category_id_{$key}", $filter['category_id']);


        }
    }
}