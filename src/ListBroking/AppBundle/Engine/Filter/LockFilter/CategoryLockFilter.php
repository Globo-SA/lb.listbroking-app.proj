<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Filter\LockFilter;

use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use ListBroking\AppBundle\Engine\Filter\LockFilterInterface;
use ListBroking\AppBundle\Exception\InvalidFilterObjectException;
use ListBroking\AppBundle\Exception\InvalidFilterTypeException;
use ListBroking\AppBundle\Form\FiltersType;

class CategoryLockFilter implements LockFilterInterface
{

    /**
     * @var int
     */
    public $type_id;

    public function __construct ($type_id)
    {
        $this->type_id = $type_id;
    }

    /**
     * @param Orx          $orX
     * @param QueryBuilder $qb
     * @param              $filters
     *
     * @throws InvalidFilterObjectException
     * @throws InvalidFilterTypeException
     * @return mixed
     */
    public function addFilter (Orx $orX, QueryBuilder $qb, $filters)
    {
        foreach ( $filters as $key => $f )
        {
            // Validate the Filter
            FiltersType::validateFilter($f);

            foreach ( $f['value'] as $filter )
            {

                if ( ! ($filter['interval'] instanceof \DateTime) )
                {
                    $filter['interval'] = new \DateTime($filter['interval']);
                }

                // Check for lock on the category
                $orX->add($qb->expr()
                             ->andX('locks.expiration_date <= CURRENT_TIMESTAMP()', 'locks.type = :category_locks_type', "locks.category = :category_locks_category_id_{$key}", "(locks.expiration_date >= :category_locks_filter_expiration_date_{$key})"))
                ;
                $qb->setParameter('category_locks_type', $this->type_id);
                $qb->setParameter("category_locks_category_id_{$key}", $filter['category']);
                $qb->setParameter("category_locks_filter_expiration_date_{$key}", $filter['interval']);
            }
        }
    }
} 