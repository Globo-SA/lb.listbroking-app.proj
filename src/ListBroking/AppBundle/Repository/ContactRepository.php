<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Lock;

class ContactRepository extends EntityRepository
{

    /**
     * Finds Contacts with expired TYPE_INITIAL_LOCK
     *
     * @param integer $limit
     *
     * @return Contact[]
     */
    public function findContactsWithExpiredInitialLock ($limit)
    {
        $qb = $this->createQueryBuilder('c');

        return $qb->join('c.lead', 'l')
                  ->join('l.locks', 'lo', 'WITH', $qb->expr()->andX()->addMultiple(
                      array(
                         'lo.type = :lock_type',
                         'lo.expiration_date <= CURRENT_TIMESTAMP()'
                        )
                    )
                  )
                  ->andWhere('c.is_ready_to_use = :is_ready_to_use')
                  ->setParameter('lock_type', Lock::TYPE_INITIAL_LOCK)
                  ->setParameter('is_ready_to_use', 0)
                  ->getQuery()
                  ->setMaxResults($limit)
                  ->execute()
            ;
    }

}