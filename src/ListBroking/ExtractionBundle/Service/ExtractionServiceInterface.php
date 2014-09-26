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
} 