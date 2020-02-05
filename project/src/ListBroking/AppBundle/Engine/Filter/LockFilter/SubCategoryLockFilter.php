<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Filter\LockFilter;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use ListBroking\AppBundle\Engine\Filter\LockFilterInterface;
use ListBroking\AppBundle\Form\FiltersType;

class SubCategoryLockFilter implements LockFilterInterface
{

    /**
     * @var int
     */
    public $type_id;

    /**
     * @var int
     */
    public $parent_id;

    public function __construct ($type_id, $parent_id)
    {
        $this->type_id = $type_id;
        $this->parent_id = $parent_id;
    }

    /**
     * @inheritdoc
     * */
    public function addFilter (Andx $andX, QueryBuilder $qb, $filters)
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

                $andX->addMultiple(array(
                    $qb->expr()
                       ->andX('locks.type = :sub_category_locks_type', "locks.sub_category = :sub_category_locks_sub_category_id_{$key}",
                           "(locks.lock_date >= :sub_category_locks_filter_expiration_date_{$key})"),
                    $qb->expr()
                       ->andX('locks.type = :sub_category_locks_category_type', "locks.category = :sub_category_locks_category_{$key}",
                           "(locks.lock_date >= :sub_category_locks_filter_expiration_date_{$key})"),
                ));

                // Query the child to get the parent
                $sub_qb = $qb->getEntityManager()
                             ->createQueryBuilder()
                ;
                $sub_qb->select('sub')
                       ->from('ListBrokingAppBundle:SubCategory', 'sub')
                       ->where('sub.id = :sub_category')
                       ->setParameter('sub_category', $filter['sub_category'])
                ;
                $sub_category = $sub_qb->getQuery()
                                       ->getOneOrNullResult()
                ;

                $qb->setParameter('sub_category_locks_type', $this->type_id);
                $qb->setParameter("sub_category_locks_sub_category_id_{$key}", $filter['sub_category']);
                $qb->setParameter("sub_category_locks_filter_expiration_date_{$key}", $filter['interval']);

                $qb->setParameter('sub_category_locks_category_type', $this->parent_id);
                $qb->setParameter("sub_category_locks_category_{$key}", $sub_category->getCategory());
            }
        }
    }
}