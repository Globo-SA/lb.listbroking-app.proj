<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ContactHistRepository extends EntityRepository implements ContactRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findByEmail(string $email): array
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ch')
            ->from('ListBrokingAppBundle:ContactHist', 'ch')
            ->where('ch.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getResult();
    }
}