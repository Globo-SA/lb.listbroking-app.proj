<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */
namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Service\Base\BaseServiceInterface;

interface ExtractionServiceInterface extends BaseServiceInterface
{

    /**
     * Find Extraction by id
     * @param $id
     *
     * @return Extraction|null
     */
    public function findExtraction($id);

    /**
     * Clones a given extraction and resets it's status
     *
     * @param Extraction $extraction
     *
     * @return Extraction
     */
    public function cloneExtraction (Extraction $extraction);

    /**
     * Returns the Query needed to find all the contacts of a given Extraction with
     * all the dimensions eagerly loaded
     *
     * @param Extraction $extraction
     *
     * @return mixed
     */
    public function getExtractionContactsQuery (Extraction $extraction);

    /**
     * Finds all the contacts of a given Extraction with
     * all the dimensions eagerly loaded
     *
     * @param Extraction $extraction
     * @param            $limit
     *
     * @return mixed
     */
    public function findExtractionContacts (Extraction $extraction, $limit = null);

    /**
     * Finds the ExtractionSummary
     *
     * @param Extraction $extraction
     *
     * @return mixed
     */
    public function findExtractionSummary (Extraction $extraction);

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
     * Compile and run the Extraction
     *
     * @param Extraction $extraction
     *
     * @throws \ListBroking\AppBundle\Exception\InvalidFilterObjectException
     * @return boolean
     */
    public function runExtraction (Extraction $extraction);

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
     * Generate locks for the contacts of a given Extraction
     *
     * @param Extraction $extraction
     * @param            $lock_types
     *
     * @return void
     */
    public function generateLocks (Extraction $extraction, $lock_types);
}