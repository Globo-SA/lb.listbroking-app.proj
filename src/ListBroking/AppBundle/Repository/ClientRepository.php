<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use ListBroking\AppBundle\Entity\Client;

class ClientRepository extends EntityRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws NonUniqueResultException
     */
    public function getById(int $id): ?Client
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from('ListBrokingAppBundle:Client', 'c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}