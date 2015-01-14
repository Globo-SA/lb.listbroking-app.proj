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


use ListBroking\TaskControllerBundle\Entity\Queue;
use Symfony\Component\Form\Form;

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

    /**
     * Gets contacts that need validation
     * @param int $limit
     * @return mixed
     */
    public function findContactsToValidate($limit = 50);

    /**
     * Validates a StagingContact using exceptions and
     * opposition lists
     * @param $contact
     * @internal param $contacts
     * @return mixed
     */
    public function validateStagingContact($contact);

    /**
     * Enriches StagingContacts using internal and external
     * processes, if only runs on valid contacts
     * @param $limit
     * @return mixed
     */
    public function enrichStatingContacts($limit = 50);

    /**
     * Handle the uploaded file and adds it to the queue
     * @param Form $form
     * @throws \Exception
     * @return Queue
     */
    public function addOppositionListFileToQueue(Form $form);
} 