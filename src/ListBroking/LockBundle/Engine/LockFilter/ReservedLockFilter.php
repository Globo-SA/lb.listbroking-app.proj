<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LockBundle\Engine\LockFilter;

use Doctrine\ORM\Query\Expr\Orx;
use ESO\Doctrine\ORM\QueryBuilder;
use ListBroking\LockBundle\Engine\LockFilterInterface;
use ListBroking\LockBundle\Exception\InvalidFilterObjectException;
use ListBroking\LockBundle\Exception\InvalidFilterTypeException;

class ReservedLockFilter implements LockFilterInterface
{

    /**
     * @var int
     */
    public $type_id;

    function __construct($type_id)
    {
        $this->type_id = $type_id;
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
            if(!array_key_exists('interval', $filter)){
                throw new InvalidFilterObjectException(
                    'Invalid filter object must be: array( \'interval\' => \'\'), in ' .
                    __CLASS__ );
            }

            if(!($filter['interval'] instanceof \DateTime)){
                $filter['interval'] = new \DateTime($filter['interval']['date'], new \DateTimeZone($filter['interval']['timezone']));
            }

            // Check for reserved locks
            $orX->add(
                $qb->expr()->andX(
                    'locks.expiration_date <= CURRENT_TIMESTAMP()',
                    'locks.type = :reserved_locks_type',
                    "(locks.expiration_date >= :reserved_locks_filter_expiration_date_{$key})"
                )
            );
            $qb->setParameter('reserved_locks_type', $this->type_id);

            $qb->setParameter("reserved_locks_filter_expiration_date_{$key}", $filter['interval']);
        }
    }

} 