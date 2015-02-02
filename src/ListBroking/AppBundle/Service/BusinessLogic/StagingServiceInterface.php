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


use ListBroking\AppBundle\Entity\StagingContact;
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
     * Gets contacts that need validation and lock them
     * to the current process
     * @param int $limit
     * @return mixed
     */
    public function findContactsToValidateAndLock($limit = 50);

    /**
     * Validates a StagingContact using exceptions and
     * opposition lists
     * @param $contact
     * @internal param $contacts
     * @return mixed
     */
    public function validateStagingContact($contact);

    /**
     * Loads validated contacts from the staging area
     * to the Lead and Contact tables
     */
    public function moveInvalidContactsToDQP();

    /**
     * Loads validated contacts from the staging area
     * to the Lead and Contact tables
     * @param StagingContact $contact
     */
    public function loadValidatedContact(StagingContact $contact);

    /**
     * Handle the uploaded file and adds it to the queue
     * @param Form $form
     * @throws \Exception
     * @return Queue
     */
    public function addOppositionListFileToQueue(Form $form);

    /**
     * Handle the uploaded StagingContacts file and adds it to the queue
     * @param Form $form
     * @throws \Exception
     * @return Queue
     */
    public function addStagingContactsFileToQueue(Form $form);

    /**
     * Imports an Opposition list by file
     * @param $type
     * @param $filename
     * @param $clear_old
     */
    public function importOppostionList($type, $filename, $clear_old);

    /**
     * Syncs the Opposition table with the Leads
     */
    public function syncContactsWithOppositionLists();

    /**
     * Used to generate a template file for importing staging contacts
     * @return string
     */
    public function getStagingContactImportTemplate();
} 