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

class CampaignLockFilter implements LockFilterInterface
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

                $orX->addMultiple(array(
                    // check for locks on the campaign
                    $qb->expr()
                       ->andX('locks.type = :campaign_locks_type', "locks.campaign = :campaign_locks_campaign_id_{$key}", "(locks.expiration_date >= :campaign_locks_filter_expiration_date_{$key})"),
                    // Check for locks on the parent (client)
                    $qb->expr()
                       ->andX('locks.type = :campaign_locks_client_type', "locks.client = :campaign_locks_client_id_{$key}", "(locks.expiration_date >= CURRENT_TIMESTAMP())")
                ))
                ;

                // Query the child to get the parent
                $sub_qb = $qb->getEntityManager()
                             ->createQueryBuilder()
                ;
                $sub_qb->select('camp')
                       ->from('ListBrokingAppBundle:Campaign', 'camp')
                       ->where('camp.id = :campaign')
                       ->setParameter('campaign', $filter['campaign'])
                ;
                $campaign = $sub_qb->getQuery()
                                   ->getOneOrNullResult()
                ;

                $qb->setParameter('campaign_locks_type', $this->type_id);
                $qb->setParameter("campaign_locks_campaign_id_{$key}", $filter['campaign']);
                $qb->setParameter("campaign_locks_filter_expiration_date_{$key}", $filter['interval']);

                $qb->setParameter('campaign_locks_client_type', $this->parent_id);
                $qb->setParameter("campaign_locks_client_id_{$key}", $campaign->getClient());
            }
        }
    }
} 