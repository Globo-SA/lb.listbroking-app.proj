<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Filter\LockFilter;

use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

use ListBroking\AppBundle\Engine\Filter\LockFilterInterface;
use ListBroking\AppBundle\Exception\InvalidFilterObjectException;
use ListBroking\AppBundle\Exception\InvalidFilterTypeException;

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
            if(!array_key_exists('client', $filter) || empty($filter['client'])
                || !array_key_exists('interval', $filter) || empty($filters['interval'])){
                continue;
            }

            if(!($filter['interval'] instanceof \DateTime)){
                $filter['interval'] = new \DateTime($filter['interval']);
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
            $qb->setParameter("client_locks_client_id_{$key}", $filter['client']);
            $qb->setParameter("client_locks_filter_expiration_date_{$key}", $filter['interval']);
        }
    }
} 