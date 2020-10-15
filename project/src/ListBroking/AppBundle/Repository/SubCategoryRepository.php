<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ListBroking\AppBundle\Repository\SubCategoryRepository\
 */
class SubCategoryRepository extends EntityRepository implements SubCategoryRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getByName(array $names): ?array
    {
        return $this->createQueryBuilder('sc')
                    ->andWhere('sc.name IN (:names)')
                    ->setParameter('names', $names)
                    ->getQuery()
                    ->getResult();
    }
}
