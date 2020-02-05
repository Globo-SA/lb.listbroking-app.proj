<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\ExtractionContact;
use ListBroking\AppBundle\Entity\Lead;

interface ExtractionContactServiceInterface
{
    /**
     * @param Contact $contact
     *
     * @return array
     */
    public function findContactExtractions(Contact $contact): array;

    /**
     * Get contact history by lead
     *
     * @param Lead $lead
     * @param bool $sold
     *
     * @return ExtractionContact[]
     */
    public function getContactHistoryByLead(Lead $lead, bool $sold = true): array;
}
