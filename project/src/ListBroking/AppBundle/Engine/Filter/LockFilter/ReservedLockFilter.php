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

class ReservedLockFilter implements LockFilterInterface
{

    /**
     * @var int
     */
    public $type_id;

    function __construct ($type_id)
    {
        $this->type_id = $type_id;
    }

    /**
     * @inheritdoc
     * */
    public function addFilter (Andx $andX, QueryBuilder $qb, $filters)
    {
        foreach ( $filters as $key => $filter )
        {
            $filter['interval'] = new \DateTime('- 1 week');

            // Validate filter array
            if ( ! array_key_exists('interval', $filter) || empty($filter['interval']) )
            {
                continue;
            }

            if ( ! ($filter['interval'] instanceof \DateTime) )
            {
                $filter['interval'] = new \DateTime($filter['interval']);
            }

            // Check for reserved locks
            $andX->add($qb->expr()
                         ->andX('locks.type = :reserved_locks_type', "(locks.expiration_date >= :reserved_locks_filter_expiration_date_{$key})"));
            $qb->setParameter('reserved_locks_type', $this->type_id);

            $qb->setParameter("reserved_locks_filter_expiration_date_{$key}", $filter['interval']);
        }
    }
}