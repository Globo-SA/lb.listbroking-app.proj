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

    /**
     * {@inheritdoc}
     */
    public function findByIdAndLead($id, int $leadId)
    {
        return $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('c')
                    ->from('ListBrokingAppBundle:Contact', 'c')
                    ->where('c.id = :id')
                    ->andWhere('c.lead = :lead_id')
                    ->setParameter('id', $id)
                    ->setParameter('lead_id', $leadId)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByLeadAndEmailAndOwner(int $leadId, string $email, string $owner)
    {
        return $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('c')
                    ->from('ListBrokingAppBundle:Contact', 'c')
                    ->join('c.owner', 'o')
                    ->where('c.email = :email')
                    ->andWhere('c.lead = :lead_id')
                    ->andWhere('o.name = :owner_name')
                    ->setParameter('email', $email)
                    ->setParameter('lead_id', $leadId)
                    ->setParameter('owner_name', $owner)
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}
