<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\ContactHist;
use ListBroking\AppBundle\Entity\ExtractionContactHist;
use ListBroking\AppBundle\Entity\LeadHist;

interface ExtractionContactHistRepositoryInterface
{
    /**
     * Return a list of extractions from a given contact
     *
     * @param ContactHist $contactHist
     *
     * @return array
     */
    public function findContactExtractions(ContactHist $contactHist): array;

    /**
     * Returns a list of phones, emails and campaigns grouped by client
     *
     * @param LeadHist $leadHist
     *
     * @return array
     */
    public function getLeadCampaignsGroupByClient(LeadHist $leadHist): array;

    /**
     * Returns extraction contacts sold by lead
     *
     * @param LeadHist $leadHist
     * @param bool $sold
     *
     * @return ExtractionContactHist[]
     */
    public function getExtractionContactsSoldByLead(LeadHist $leadHist, bool $sold): array;
}
