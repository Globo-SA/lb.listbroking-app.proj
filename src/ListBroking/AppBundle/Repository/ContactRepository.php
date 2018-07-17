<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ContactRepository extends EntityRepository implements ContactRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findByEmail(string $email): array
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from('ListBrokingAppBundle:Contact', 'c')
            ->where('c.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getResult();
    }
}