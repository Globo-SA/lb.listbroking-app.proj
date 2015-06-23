<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\BusinessLogic;

use Doctrine\ORM\Query;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionTemplate;
use ListBroking\AppBundle\Exception\InvalidExtractionException;

interface ExtractionServiceInterface
{

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
    public function handleFiltration (Extraction $extraction);

    /**
     * Used the LockService to compile and run the Extraction
     *
     * @param Extraction $extraction
     *
     * @throws \ListBroking\AppBundle\Exception\InvalidFilterObjectException
     * @return boolean
     */
    public function runExtraction (Extraction $extraction);

    /**
     * Executes the filtering engine and adds the contacts
     * to the Extraction
     *
     * @param Extraction $extraction
     *
     * @return void
     * @throws \ListBroking\AppBundle\Exception\InvalidFilterObjectException
     */
    public function executeFilterEngine (Extraction $extraction);

    /**
     * @param Extraction $extraction
     *
     * @return mixed
     */
    public function getExtractionSummary (Extraction $extraction);

    /**
     * Gets all the contacts of a given Extraction with
     * all the dimensions eagerly loaded
     *
     * @param Extraction $extraction
     * @param            $limit
     *
     * @return mixed
     */
    public function getExtractionContacts (Extraction $extraction, $limit);

    /**
     * Exports Leads to file
     *
     * @param Extraction $extraction
     * @param            $extraction_template ExtractionTemplate
     * @param array      $info
     *
     * @throws InvalidExtractionException
     * @internal param $type
     * @return mixed
     */
    public function exportExtraction (Extraction $extraction, ExtractionTemplate $extraction_template, $info = array());

    /**
     * Used to import a file with Leads
     *
     * @param $filename
     *
     * @internal param $filename
     * @return mixed
     */
    public function importExtraction ($filename);

    /**
     * Persists Deduplications to the database, this function uses PHPExcel with APC
     *
     * @param Extraction $extraction
     * @param string     $filename
     * @param string     $field
     *
     * @return void
     */
    public function uploadDeduplicationsByFile (Extraction $extraction, $filename, $field);

    /**
     * Removes Deduplicated Leads from an Extraction
     * using the ExtractionDeduplication Entity
     *
     * @param Extraction $extraction
     *
     * @return mixed
     */
    public function deduplicateExtraction (Extraction $extraction);

    /**
     * Generate locks for the contacts of a given Extraction
     *
     * @param Extraction $extraction
     * @param            $lock_types
     *
     * @return mixed
     */
    public function generateLocks (Extraction $extraction, $lock_types);

    /**
     * Delivers the Extraction to a set of Emails
     *
     * @param Extraction $extraction
     * @param            $emails
     * @param            $filename
     *
     * @return mixed
     */
    public function deliverExtraction (Extraction $extraction, $emails, $filename);

    /**
     * Clones a given extraction and resets it's status
     *
     * @param Extraction $extraction
     *
     * @return Extraction
     */
    public function cloneExtraction (Extraction $extraction);
} 