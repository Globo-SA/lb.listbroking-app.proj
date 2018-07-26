<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\Contact;
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
}