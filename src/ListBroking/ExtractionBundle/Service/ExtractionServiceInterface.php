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
     * Set the Extraction filters
     * @param $id
     * @param $filters
     * @internal param $lock_filters
     * @internal param $contact_filters
     * @return mixed
     */
    public function setExtractionFilters($id, $filters);

    /**
     * Adds a Lock Filter to an Extraction
     * @param $id
     * @param $type
     * @param $new_filters
     * @internal param $filter
     * @return mixed
     */
    public function addExtractionLockFilters($id, $type, $new_filters);

    /**
     * Adds a Contact Filter to an Extraction
     * @param $id
     * @param $type
     * @param $new_filters
     * @internal param $filter
     * @return mixed
     */
    public function addExtractionContactFilters($id, $type, $new_filters);

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
     * Exports Leads using a given type
     * @param $extraction_template
     * @param $leads_array
     * @param $type
     * @param array $info
     * @return mixed
     */
    public function exportExtraction($extraction_template, $leads_array, $type, $info = array());

    /**
     * Used to import a file with Leads
     * @param $filename
     * @internal param $filename
     * @return mixed
     */
    public function importExtraction($filename);
} 