<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\ExtractionBundle\Service;


use ListBroking\ExtractionBundle\Entity\Extraction;
use ListBroking\ExtractionBundle\Entity\ExtractionTemplate;
use ListBroking\ExtractionBundle\Exception\InvalidExtractionException;

interface ExtractionServiceInterface {
    
    /**
     * Gets list of extractions
     * @param bool $only_active
     * @return mixed
     */
    public function getExtractionList($only_active = false);

    /**
     * Gets a single extraction
     * @param $id
     * @param $hydrate
     * @return mixed
     */
    public function getExtraction($id, $hydrate = false);

    /**
     * Adds a single extraction
     * @param $extraction
     * @return mixed
     */
    public function addExtraction($extraction);

    /**
     * Removes a single extraction
     * @param $id
     * @return mixed
     */
    public function removeExtraction($id);

    /**
     * Updates a single country
     * @param $extraction
     * @return mixed
     */
    public function updateExtraction($extraction);

    /**
     * Gets list of extraction_templates
     * @param bool $only_active
     * @return mixed
     */
    public function getExtractionTemplateList($only_active = false);

    /**
     * Gets a single extraction_template
     * @param $id
     * @param $hydrate
     * @return mixed
     */
    public function getExtractionTemplate($id, $hydrate = false);

    /**
     * Adds a single extraction_template
     * @param $extraction_template
     * @return mixed
     */
    public function addExtractionTemplate($extraction_template);

    /**
     * Removes a single extraction_template
     * @param $id
     * @return mixed
     */
    public function removeExtractionTemplate($id);

    /**
     * Updates a single extraction_template
     * @param $extraction_template
     * @return mixed
     */
    public function updateExtractionTemplate($extraction_template);

    /**
     * Used the LockService to compile and run the Extraction
     * @param Extraction $extraction
     * @return mixed
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
     * Set the Extraction filters
     * @param Extraction $extraction
     * @param $filters
     * @internal param $id
     * @internal param $lock_filters
     * @internal param $contact_filters
     * @return mixed
     */
    public function setExtractionFilters(Extraction $extraction, $filters);

    /**
     * Associates an array of contacts to an extraction
     * If merge = false old contacts will be removed
     * @param $extraction Extraction
     * @param $contacts
     * @param bool $merge
     */
    public function addExtractionContacts($extraction, $contacts, $merge = false);

    /**
     * Adds Leads to the Lead Filter of a given Extraction
     * @param Extraction $extraction
     * @param $leads_array
     */
    public function excludeLeads(Extraction $extraction, $leads_array);

    /**
     * Gets all the Existing Export Types
     * @return array
     */
    public function getExportTypes();

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
} 