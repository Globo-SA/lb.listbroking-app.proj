<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\BusinessLogic;


interface StagingServiceInterface {

    /**
     * Adds a new staging contact inferring
     * the fields by the array key
     * @param $data_array
     * @return mixed
     */
    public function addStagingContact($data_array);

    /**
     * Imports contacts from a file to the staging area
     * @param $filename
     * @return mixed
     */
    public function importStagingContacts($filename);
} 