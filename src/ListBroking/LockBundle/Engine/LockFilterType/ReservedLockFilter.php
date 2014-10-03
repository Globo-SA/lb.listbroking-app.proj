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

class ReservedLockFilter implements LockFilterInterface
{

    /**
     * @var int
     */
    public $type_id;

    /**
     * @var int
     */
    public $parent;

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
                throw new InvalidFilterObjectException('Invalid filter object must be:' . json_encode(array('interval' => '')));
            }
            if(!($filter['interval'] instanceof \DateTime)){
                throw new InvalidFilterTypeException('The filter interval field must be an instance of \DateTime()');
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

            // Step up to the parent and filter
            if($this->parent){
                $this->parent->addFilter($orX, $qb, array($filter));
            }
        }
    }

} 