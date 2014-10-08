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

class ClientLockFilter implements LockFilterInterface {

    /**
     * @var int
     */
    public $type_id;

    /**
     * @var int
     */
    public $parent;

    public function __construct($type_id)
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
            if(!array_key_exists('client_id', $filter)
                || !array_key_exists('interval', $filter)){
                throw new InvalidFilterObjectException(
                    'Invalid filter object must be: array(\'client_od\' => \'\', \'interval\' => \'\'), in ' .
                    __CLASS__ );
            }

            if(!($filter['interval'] instanceof \DateTime)){
                throw new InvalidFilterTypeException(
                    'The filter interval field must be an instance of \DateTime(), in '
                    . __CLASS__);
            }


            // Check for locks on the client
            $orX->add(
                $qb->expr()->andX(
                    'locks.expiration_date <= CURRENT_TIMESTAMP()',
                    'locks.type = :client_locks_type',
                    "locks.client = :client_locks_client_id_{$key}",
                    "(locks.expiration_date >= :client_locks_filter_expiration_date_{$key})"
                )
            );
            $qb->setParameter('client_locks_type', $this->type_id);
            $qb->setParameter("client_locks_client_id_{$key}", $filter['client_id']);
            $qb->setParameter("client_locks_filter_expiration_date_{$key}", $filter['interval']);
        }
    }
} 