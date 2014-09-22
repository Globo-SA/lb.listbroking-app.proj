<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\ClientBundle\Repository\ORM;

use Doctrine\ORM\QueryBuilder;
use ListBroking\ClientBundle\Repository\CampaignRepositoryInterface;
use ListBroking\DoctrineBundle\Repository\ORM\BaseEntityRepository;

class CampaignRepository extends BaseEntityRepository implements CampaignRepositoryInterface
{
    /**
     * @param bool $only_active
     * @return array|mixed
     */
    public function findAll($only_active = true)
    {
        /** @var QueryBuilder $query_builder */
        $query_builder = $this->createQueryBuilder();

        /* By default only show active entities */
        if ($only_active)
        {
            $query_builder
                ->andWhere($this->alias() . '.is_active = :id')
                ->setParameter('is_active', 1);
        }
        return $query_builder->getQuery()->getResult();
    }
}