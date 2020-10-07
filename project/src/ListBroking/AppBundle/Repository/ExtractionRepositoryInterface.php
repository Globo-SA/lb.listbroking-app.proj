<?php

namespace ListBroking\AppBundle\Repository;

use DateTime;
use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\RevenueFilter;
use ListBroking\AppBundle\Model\ExtractionFilter;

interface ExtractionRepositoryInterface
{
    /**
     * Clones a given extraction and resets it's status
     *
     * @param Extraction $extraction
     *
     * @return Extraction
     */
    public function cloneExtraction(Extraction $extraction);

    /**
     * Associates multiple contacts to an extraction
     *
     * @param     $extraction Extraction
     * @param     $contacts
     * @param int $batch_size
     *
     * @return mixed
     */
    public function addContacts(Extraction $extraction, $contacts, $batch_size = 1000);

    /**
     * @param DateTime|string $start_date
     * @param DateTime|string $end_date
     * @param int             $page
     * @param int             $limit
     *
     * @return array|null
     */
    public function getActiveCampaigns($start_date, $end_date, $page = 1, $limit = 50);

    /**
     * Get revenue between two dates
     *
     * @param RevenueFilter $filter
     *
     * @return array|null
     */
    public function getRevenue(RevenueFilter $filter);

    /**
     * Find the most recent extraction before $date
     *
     * @param $date
     *
     * @return Extraction|null
     */
    public function findLastExtractionBeforeDate($date);

    /**
     * Returns all extractions in filtering state
     *
     * @param string $name
     *
     * @return array
     *
     */
    public function findExtractionsByName(string $name): array;

    /**
     * Creates new Extraction
     *
     * @param Campaign         $campaign
     * @param ExtractionFilter $extractionFilter
     *
     * @return Extraction
     */
    public function createExtraction(Campaign $campaign, ExtractionFilter $extractionFilter): Extraction;
}
