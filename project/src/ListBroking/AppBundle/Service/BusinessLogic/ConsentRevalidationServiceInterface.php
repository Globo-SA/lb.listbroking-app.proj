<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Contact;

interface ConsentRevalidationServiceInterface
{
    /**
     * Revalidates the consent for X random contacts from a given country and year
     * It returns the number of contacts available to revalidate
     *
     * Note: When sending a contact ID, it will reprocess only one contact
     * but he needs to obey the other criteria too (year, owner, etc)
     *
     * @param int      $year
     * @param string   $countryCode
     * @param string   $owner
     * @param int      $limit
     * @param int|null $contactId
     *
     * @return Contact[]
     */
    public function revalidateWithIVR(
        int $year,
        string $countryCode,
        string $owner,
        int $limit,
        int $contactId = null
    ): array;

    /**
     * Updates the revalidation request by accepting the consent
     *
     * @param int $id
     */
    public function acceptConsent(int $id): void;

    /**
     * Updates the revalidation request by rejecting the consent
     *
     * @param int $id
     */
    public function rejectConsent(int $id): void;
}