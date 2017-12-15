<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Source;

/**
 * ListBroking\AppBundle\Repository\SourceRepository
 */
class SourceRepository extends EntityRepository implements SourceRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getByExternalId(string $externalId) : ?Source
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.external_id = :externalId')
            ->setParameter('externalId', $externalId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
