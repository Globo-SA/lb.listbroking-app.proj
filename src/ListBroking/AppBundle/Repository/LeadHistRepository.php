<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Lock;

class LeadHistRepository extends EntityRepository implements LeadRepositoryInterface
{

    /**
     * {@inheritdoc}
     */
    public function syncLeadsWithOppositionLists ()
    {

        // Set in_opposition for every matched phone
        $sql = <<<SQL
            UPDATE lead_hist lh
            JOIN opposition_list ol on lh.phone = ol.phone
            SET lh.in_opposition = 1,
                lh.updated_at = now()
SQL;

        /** @var Statement $stmt */
        $stmt = $this->getEntityManager()
                     ->getConnection()
                     ->prepare($sql)
        ;
        $stmt->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findLeadsWithExpiredInitialLock ($limit)
    {
        $qb = $this->createQueryBuilder('lh');

        return $qb
                  ->leftJoin('lh.locks', 'lo', 'WITH', $qb->expr()
                                                         ->andX()
                                                         ->addMultiple(array(
                                                             'lo.type = :lock_type',
                                                             'lo.expiration_date >= CURRENT_TIMESTAMP()'
                                                         )))
                  ->andWhere('lo.id IS NULL')
                  ->andWhere('lh.is_ready_to_use = :is_ready_to_use')
                  ->setParameter('lock_type', Lock::TYPE_INITIAL_LOCK)
                  ->setParameter('is_ready_to_use', 0)

                  ->groupBy('lh.id')
                  ->getQuery()
                  ->setMaxResults($limit)
                  ->execute()
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function getByPhone(string $phone): array
    {
        return $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('lh')
                    ->from('ListBrokingAppBundle:LeadHist', 'lh')
                    ->where('lh.phone = :phone')
                    ->setParameter('phone', $phone)
                    ->getQuery()
                    ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function updateInOppositionByPhone(string $phone, bool $inOpposition)
    {
        return $this->getEntityManager()
                    ->createQueryBuilder()
                    ->update('ListBrokingAppBundle:LeadHist', 'lh')
                    ->set('lh.in_opposition', ':in_opposition')
                    ->set('lh.updated_at', 'CURRENT_TIMESTAMP()')
                    ->where('lh.phone = :phone')
                    ->setParameter('in_opposition', $inOpposition)
                    ->setParameter('phone', $phone)
                    ->getQuery()
                    ->getResult();
    }
}
