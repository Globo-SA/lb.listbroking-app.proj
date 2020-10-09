<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionContact;
use ListBroking\AppBundle\Entity\Lead;

interface ExtractionContactRepositoryInterface
{
    /**
     * Return a list of extractions from a given contact
     *
     * @param Contact $contact
     *
     * @return array
     */
    public function findContactExtractions(Contact $contact): array;

    /**
     * Returns a group_concated list of phones, emails and campaigns grouped by client
     *
     * @param Lead $lead
     *
     * @return array
     */
    public function getLeadCampaignsGroupByClient(Lead $lead): array;

    /**
     * Returns extraction contacts sold by lead
     *
     * @param Lead $lead
     * @param bool $sold
     *
     * @return ExtractionContact[]
     */
    public function getExtractionContactsSoldByLead(Lead $lead, bool $sold): array;

    /**
     * Get contacts from a given extraction.
     *
     * @param Extraction $extraction
     * @param array      $fields
     * @param int        $limit
     * @param int        $offset
     *
     * @return array
     */
    public function getExtractionContacts(
        Extraction $extraction,
        array $fields = [],
        int $limit = 1000,
        int $offset = 0
    ): array;
}
