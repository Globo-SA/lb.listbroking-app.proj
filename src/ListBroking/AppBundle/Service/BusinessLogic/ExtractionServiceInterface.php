<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use Doctrine\ORM\Query;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionLog;
use ListBroking\AppBundle\Entity\RevenueFilter;
use ListBroking\AppBundle\Service\Base\BaseServiceInterface;
use ListBroking\AppBundle\Service\Helper\FileHandlerServiceInterface;

interface ExtractionServiceInterface extends BaseServiceInterface
{

    /**
     * Finds the last ExtractionLog for a given Extraction
     *
     * @param Extraction $extraction
     * @param            $limit
     *
     * @return ExtractionLog[]
     */
    public function findLastExtractionLog(Extraction $extraction, $limit);

    /**
     * Find Extraction by id
     *
     * @param $id
     *
     * @return Extraction|null
     */
    public function findExtraction($id);

    /**
     * @param $start_date
     * @param $end_date
     * @param $page
     * @param $limit
     *
     * @return mixed
     */
    public function getActiveCampaigns($start_date, $end_date, $page, $limit);

    /**
     * Get revenue
     *
     * @param RevenueFilter $filter
     *
     * @return array|null
     */
    public function getRevenue(RevenueFilter $filter);

    /**
     * Return all extractions
     *
     * @param string $name
     *
     * @return Extraction[]
     */
    public function findExtractionsByName(string $name);

    /**
     * Clones a given extraction and resets it's status
     *
     * @param Extraction $extraction
     *
     * @return Extraction
     */
    public function cloneExtraction(Extraction $extraction);

    /**
     * Returns the Query needed to find all the contacts of a given Extraction with
     * all the dimensions eagerly loaded
     *
     * @param Extraction $extraction
     * @param null       $fetch_mode
     *
     * @return Query
     */
    public function getExtractionContactsQuery(Extraction $extraction, $fetch_mode = null);

    /**
     * Uses a FileHandlerService to export contacts of a given Extraction
     *
     * @param FileHandlerServiceInterface $file_service
     * @param Extraction                  $extraction
     * @param array                       $template
     * @param int                         $batch_size
     *
     */
    public function exportExtractionContacts(
        FileHandlerServiceInterface $file_service,
        Extraction $extraction,
        $template,
        $batch_size
    );

    /**
     * Finds all the contacts of a given Extraction with
     * all the dimensions eagerly loaded
     *
     * @param Extraction $extraction
     * @param            $limit
     *
     * @return mixed
     */
    public function findExtractionContacts(Extraction $extraction, $limit = null);

    /**
     * Finds the ExtractionSummary
     *
     * @param Extraction $extraction
     *
     * @return mixed
     */
    public function findExtractionSummary(Extraction $extraction);

    /**
     * Handles Extraction Filtration
     *  . Saves new Filters
     *  . Marks Extraction to be Extracted
     *  . Sets the Extraction Status to CONFIRMATION
     *
     * @param Extraction $extraction
     *
     * @return bool Returns true if the extraction is ready to be processed by a consumer
     */
    public function handleFiltration(Extraction $extraction);

    /**
     * Compile and run the Extraction
     *
     * @param Extraction $extraction
     *
     * @throws \ListBroking\AppBundle\Exception\InvalidFilterObjectException
     * @return boolean
     */
    public function runExtraction(Extraction $extraction);

    /**
     * Persists Deduplications to the database, this function uses PHPExcel with APC
     *
     * @param Extraction $extraction
     * @param \PHPExcel  $file
     * @param string     $field
     *
     * @return void
     */
    public function uploadDeduplicationsByFile(Extraction $extraction, \PHPExcel $file, $field);

    /**
     * Removes Deduplications associated with a given Extraction from the database.
     *
     * @param Extraction $extraction
     *
     * @return void
     */
    public function removeDeduplications(Extraction $extraction);

    /**
     * Generate locks for the contacts of a given Extraction
     *
     * @param Extraction $extraction
     * @param            $lock_types
     *
     * @return void
     */
    public function generateLocks(Extraction $extraction, $lock_types);

    /**
     * Logs an occurred action of a given Extraction
     *
     * @param Extraction $extraction
     * @param            $message
     *
     * @return ExtractionLog
     */
    public function logExtractionAction(Extraction $extraction, $message);

    /**
     * @param Extraction $extraction
     *
     * @return mixed
     */
    public function generateContactCampaignHistory(Extraction $extraction);
}
