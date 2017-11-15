<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Repository;

use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Entity\Lock;

class LeadRepository extends EntityRepository
{

    /**
     * Synchronizes Leads with opposition lists
     * @throws \Doctrine\DBAL\DBALException
     */
    public function syncLeadsWithOppositionLists ()
    {

        // Set in_opposition for every matched phone
        $sql = <<<SQL
            UPDATE lead l
            JOIN opposition_list ol on l.phone = ol.phone
            SET l.in_opposition = 1,
                l.updated_at = now()
SQL;

        /** @var Statement $stmt */
        $stmt = $this->getEntityManager()
                     ->getConnection()
                     ->prepare($sql)
        ;
        $stmt->execute();
    }

    /**
     * Finds Leads with expired TYPE_INITIAL_LOCK
     *
     * @param integer    $limit
     *
     * @return \ListBroking\AppBundle\Entity\Lead[]
     */
    public function findLeadsWithExpiredInitialLock ($limit)
    {
        $qb = $this->createQueryBuilder('l');

        return $qb
                  ->leftJoin('l.locks', 'lo', 'WITH', $qb->expr()
                                                         ->andX()
                                                         ->addMultiple(array(
                                                             'lo.type = :lock_type',
                                                             'lo.expiration_date >= CURRENT_TIMESTAMP()'
                                                         )))
                  ->andWhere('lo.id IS NULL')
                  ->andWhere('l.is_ready_to_use = :is_ready_to_use')
                  ->setParameter('lock_type', Lock::TYPE_INITIAL_LOCK)
                  ->setParameter('is_ready_to_use', 0)

                  ->groupBy('l.id')
                  ->getQuery()
                  ->setMaxResults($limit)
                  ->execute()
            ;
    }

    /**
     * Finds an leads by phone. The phone is unique by country
     *
     * @param string $phone
     *
     * @return null|Lead
     */
    public function findByPhone(string $phone): ?Lead
    {
        return $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('l')
                    ->from('ListBrokingAppBundle:Lead', 'l')
                    ->where('l.phone = :phone')
                    ->setParameter('phone', $phone)
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}
