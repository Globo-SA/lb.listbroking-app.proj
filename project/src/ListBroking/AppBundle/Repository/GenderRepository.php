<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ListBroking\AppBundle\Repository\GenderRepository
 */
class GenderRepository extends EntityRepository implements GenderRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getByName(array $names): ?array
    {
        return $this->createQueryBuilder('g')
                    ->andWhere('g.name IN (:names)')
                    ->setParameter('names', $names)
                    ->getQuery()
                    ->getResult();
    }
}
