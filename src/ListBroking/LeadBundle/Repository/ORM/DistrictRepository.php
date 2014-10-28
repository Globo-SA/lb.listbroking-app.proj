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


use ListBroking\DoctrineBundle\Repository\ORM\BaseEntityRepository;
use ListBroking\LeadBundle\Repository\DistrictRepositoryInterface;

class DistrictRepository extends BaseEntityRepository implements DistrictRepositoryInterface {
    /**
     * @param $name
     * @param bool $hydrate
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDistrictByName($name, $hydrate = false)
    {
        $query_builder = $this->createQueryBuilder()
            ->andWhere("{$this->alias()}.name = :name");

        $query_builder->setParameter('name', $name);
        if ($hydrate){
            return $query_builder->getQuery()->getOneOrNullResult();
        }

        return $query_builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
    }
} 