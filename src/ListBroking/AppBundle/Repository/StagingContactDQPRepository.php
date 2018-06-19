<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Behavior\DateSearchableRepositoryBehavior;

class StagingContactDQPRepository extends EntityRepository implements StagingContactDQPRepositoryInterface
{

    use DateSearchableRepositoryBehavior;

    /**
     * {@inheritdoc}
     */
    public function cleanUp($id)
    {
        return $this->createQueryBuilder('scd')
                    ->delete('ListBrokingAppBundle:StagingContactDQP' ,'scd')
                    ->where('scd.id <= :id')
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
            ->delete('ListBrokingAppBundle:StagingContactDQP' ,'scd')
            ->where('scd.email = :email OR scd.phone = :phone')
            ->setParameter('email', $email)
            ->setParameter('phone', $phone)
            ->getQuery()
            ->execute();
    }
}

