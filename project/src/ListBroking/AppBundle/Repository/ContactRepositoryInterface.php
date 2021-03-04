<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\Contact;

interface ContactRepositoryInterface
{
    /**
     * @param string $email
     *
     * @return array
     */
    public function findByEmail(string $email): array;

    /**
     * @param     $id
     * @param int $leadId
     *
     * @return mixed
     */
    public function findByIdAndLead($id, $leadId);

    public function findByLeadAndEmailAndOwner($leadId, $email, $owner);

    /**
     * Find random contacts by year that don't have any revalidation history
     *
     * @param int      $year
     * @param string   $countryCode
     * @param string   $owner
     * @param int      $limit
     * @param int|null $contactId
     *
     * @return Contact[]
     */
    public function getRandomContactsWithoutConsentRevalidations(
        int $year,
        string $countryCode,
        string $owner,
        int $limit,
        int $contactId = null
    ): array;

    /**
     * Persists a Contact
     *
     * @param Contact $contact
     * @return Contact
     */
    public function saveContact(Contact $contact): Contact;
}
