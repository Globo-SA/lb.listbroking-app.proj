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

class CampaignLockFilter implements LockFilterInterface {

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
            if(!array_key_exists('campaign_id', $filter)
                || !array_key_exists('interval', $filter)){
                throw new InvalidFilterObjectException(
                    'Invalid filter object must be: array(\'campaign_id\' => \'\', \'interval\' => \'\'), in ' .
                    __CLASS__ );
            }

            if(!($filter['interval'] instanceof \DateTime)){
                $filter['interval'] = new \DateTime($filter['interval']['date'], new \DateTimeZone($filter['interval']['timezone']));
            }

            $orX->addMultiple(
                array(
                    // check for locks on the campaign
                    $qb->expr()->andX(
                        'locks.expiration_date <= CURRENT_TIMESTAMP()',
                        'locks.type = :campaign_locks_type',
                        "locks.campaign = :campaign_locks_campaign_id_{$key}",
                        "(locks.expiration_date >= :campaign_locks_filter_expiration_date_{$key})"
                    ),
                    // Check for locks on the parent (client)
                    $qb->expr()->andX(
                        'locks.type = :campaign_locks_client_type',
                        "locks.client = :campaign_locks_client_id_{$key}",
                        "(locks.expiration_date >= CURRENT_TIMESTAMP())"
                    )
                )
            );
            $qb->setParameter('campaign_locks_type', $this->type_id);
            $qb->setParameter("campaign_locks_campaign_id_{$key}", $filter['campaign_id']);
            $qb->setParameter("campaign_locks_filter_expiration_date_{$key}", $filter['interval']);

            $qb->setParameter('campaign_locks_client_type', $this->parent_id);
            $qb->setParameter("campaign_locks_client_id_{$key}", $filter['client_id']);
        }
    }
} 