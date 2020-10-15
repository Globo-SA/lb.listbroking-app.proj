<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ListBroking\AppBundle\Repository\DistrictRepository
 */
class DistrictRepository extends EntityRepository implements DistrictRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getByName(array $names): ?array
    {
        return $this->createQueryBuilder('d')
                    ->andWhere('d.name IN (:names)')
                    ->setParameter('names', $names)
                    ->getQuery()
                    ->getResult();
    }
}
