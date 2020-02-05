<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Behavior\DateSearchableRepositoryBehavior;

class StagingContactProcessedRepository extends EntityRepository implements StagingContactProcessedRepositoryInterface
{
    use DateSearchableRepositoryBehavior;

    /**
     * {@inheritdoc}
     */
    public function cleanUp($id)
    {
        return $this->createQueryBuilder('scp')
                    ->delete('ListBrokingAppBundle:StagingContactProcessed' ,'scp')
                    ->where('scp.id <= :id')
                    ->setParameter('id', $id)
                    ->getQuery()
                    ->execute();
    }

    /**
     * @param string $email
     * @param string $phone
     *
     * @return mixed
     */
    public function deleteContactByEmailOrPhone(string $email, string $phone)
    {
        return $this->createQueryBuilder('scd')
            ->delete('ListBrokingAppBundle:StagingContactProcessed' ,'scd')
            ->where('scd.email = :email OR scd.phone = :phone')
            ->setParameter('email', $email)
            ->setParameter('phone', $phone)
            ->getQuery()
            ->execute();
    }

}

