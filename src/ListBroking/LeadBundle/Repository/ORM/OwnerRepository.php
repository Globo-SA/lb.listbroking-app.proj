<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LeadBundle\Repository\ORM;


use Doctrine\ORM\AbstractQuery;
use ListBroking\DoctrineBundle\Repository\ORM\BaseEntityRepository;
use ListBroking\LeadBundle\Repository\OwnerRepositoryInterface;

class OwnerRepository extends BaseEntityRepository implements OwnerRepositoryInterface
{
    /**
     * @param $owner
     * @param bool $hydrate
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOwnerByName($owner, $hydrate = false)
    {
        $query_builder = $this->createQueryBuilder()
            ->andWhere("{$this->alias()}.name = :name");

        $query_builder->setParameter('name', $owner);
        if ($hydrate){
            return $query_builder->getQuery()->getOneOrNullResult();
        }

        return $query_builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }
} 