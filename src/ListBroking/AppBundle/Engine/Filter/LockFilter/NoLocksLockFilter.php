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

class NoLocksLockFilter implements LockFilterInterface
{

    /**
     * @var int
     */
    public $type_id;

    /**
     * NoLocksLockFilter constructor.
     *
     * @param string $type_id
     */
    function __construct ($type_id)
    {
        $this->type_id = $type_id;
    }

    /**
     * @inheritdoc
     * */
    public function addFilter (Andx $andX, QueryBuilder $qb, $filters)
    {
        /**
         * Lock Type isn't used for this filter !!
         */
        foreach ( $filters as $key => $filter )
        {
            $filter['interval'] = new \DateTime();

            // Validate filter array
            if ( ! array_key_exists('interval', $filter) || empty($filter['interval']) )
            {
                continue;
            }

            if ( ! ($filter['interval'] instanceof \DateTime) )
            {
                $filter['interval'] = new \DateTime($filter['interval']);
            }

            // Check for all locks
            $andX->add($qb->expr()
                          ->andX("(locks.expiration_date >= :no_locks_locks_filter_expiration_date_{$key})"));
            $qb->setParameter("no_locks_locks_filter_expiration_date_{$key}", $filter['interval']);
        }
    }
} 