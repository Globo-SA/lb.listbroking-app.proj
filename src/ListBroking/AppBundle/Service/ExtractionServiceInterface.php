<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service;


use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionDeduplicationQueue;
use ListBroking\AppBundle\Entity\ExtractionTemplate;
use ListBroking\AppBundle\Exception\InvalidExtractionException;
use ListBroking\AppBundle\Form\ExtractionDeduplicationType;
use Symfony\Component\Form\Form;

interface ExtractionServiceInterface {

    /**
     * Used the LockService to compile and run the Extraction
     * @param Extraction $extraction
     * @return void
     */
    public function runExtraction(Extraction $extraction);

    /**
     * Gets all the contacts of a given Extraction with
     * all the dimensions eagerly loaded
     * @param Extraction $extraction
     * @return mixed
     */
    public function getExtractionContacts(Extraction $extraction);


    /**
     * Exports Leads using a given type
     * @param $extraction_template ExtractionTemplate
     * @param $contacts
     * @param array $info
     * @throws InvalidExtractionException
     * @internal param $type
     * @return mixed
     */
    public function exportExtraction(ExtractionTemplate $extraction_template, $contacts, $info = array());

    /**
     * Used to import a file with Leads
     * @param $filename
     * @internal param $filename
     * @return mixed
     */
    public function importExtraction($filename);

    /**
     * Handle the uploaded file and adds it to the queue
     * @param Extraction $extraction
     * @param Form $form
     * @return ExtractionDeduplicationQueue
     */
    public function addDeduplicationFileToQueue(Extraction $extraction, Form $form);

    /**
     * Persists Deduplications to the database, this function uses PHPExcel with APC
     * @param Extraction $extraction
     * @param string $filename
     * @param string $field
     * @param $merge
     * @return void
     */
    public function uploadDeduplicationsByFile(Extraction $extraction, $filename, $field, $merge);

    /**
     * Get Deduplication Queue by Extraction
     * @param Extraction $extraction
     * @param bool $hydrate
     * @return mixed
     */
    public function getDeduplicationQueuesByExtraction(Extraction $extraction, $hydrate = true);

    /**
     * Removes Deduplicated Leads from an Extraction
     * using the ExtractionDeduplication Entity
     * @param Extraction $extraction
     * @return mixed
     */
    public function deduplicateExtraction(Extraction $extraction);

    /**
     * Generate locks for the contacts of a given Extraction
     * @param Extraction $extraction
     * @param $lock_types
     * @return mixed
     */
    public function generateLocks(Extraction $extraction, $lock_types);

    /**
     * Delivers the Extraction to a set of Emails
     * @param Extraction $extraction
     * @param $emails
     * @return mixed
     */
    public function deliverExtraction(Extraction $extraction, $emails);
    /**
     * Adds Leads to the Lead Filter of a given Extraction
     * @param Extraction $extraction
     * @param $leads_array
     * @param string $field
     */
    //public function excludeLeads(Extraction $extraction, $leads_array, $field = 'id');
} 