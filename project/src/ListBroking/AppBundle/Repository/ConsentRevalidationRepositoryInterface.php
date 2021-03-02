<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\ConsentRevalidation;

interface ConsentRevalidationRepositoryInterface
{
    /**
     * Persist a ConsentRevalidation object
     *
     * @param ConsentRevalidation $consentRevalidation
     *
     * @return ConsentRevalidation
     */
    public function saveConsentRevalidation(ConsentRevalidation $consentRevalidation): ConsentRevalidation;

    /**
     * Get a ConsentRevalidation object by ID
     *
     * Note: The entity_manager already have a generic "find" method
     * but we want to use this interface to encapsulate that implementation.
     *
     * @param int $id
     *
     * @return ConsentRevalidation|null
     */
    public function getById(int $id): ?ConsentRevalidation;
}