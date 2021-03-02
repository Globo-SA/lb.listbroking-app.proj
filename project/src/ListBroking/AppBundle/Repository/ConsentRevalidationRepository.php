<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use ListBroking\AppBundle\Entity\ConsentRevalidation;

class ConsentRevalidationRepository extends EntityRepository implements ConsentRevalidationRepositoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws OptimisticLockException
     */
    public function saveConsentRevalidation(ConsentRevalidation $consentRevalidation): ConsentRevalidation
    {
        $this->getEntityManager()->persist($consentRevalidation);
        $this->getEntityManager()->flush();

        return $consentRevalidation;
    }

    /**
     * {@inheritDoc}
     */
    public function getById(int $id): ?ConsentRevalidation
    {
        $consentRevalidation = $this->find($id);

        return $consentRevalidation instanceof ConsentRevalidation
            ? $consentRevalidation
            : null;
    }
}
