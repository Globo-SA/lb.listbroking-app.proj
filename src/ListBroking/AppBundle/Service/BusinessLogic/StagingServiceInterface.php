<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */
namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\StagingContact;

interface StagingServiceInterface
{

    /**
     * Adds a new staging contact inferring
     * the fields by the array key
     *
     * @param $data_array
     *
     * @return StagingContact
     */
    public function addStagingContact ($data_array);

    /**
     * Finds contacts that need validation and lock them
     * to the current process
     *
     * @param int $limit
     *
     * @return StagingContact[]
     */
    public function findAndLockContactsToValidate ($limit = 50);

    /**
     * Imports an Opposition list by file
     *
     * @param $type
     * @param $file
     * @param $clear_old
     */
    public function importOppositionList ($type, $file, $clear_old);

    /**
     * Imports contacts from a file to the staging area
     *
     * @param $file
     *
     * @return mixed
     */
    public function importStagingContacts ($file);

    /**
     * Loads validated contacts from the staging area
     * to the Lead and Contact tables
     *
     * @param StagingContact $contact
     */
    public function loadValidatedContact (StagingContact $contact);

    /**
     * Loads validated contacts from the staging area
     * to the Lead and Contact tables
     */
    public function moveInvalidContactsToDQP ();

    /**
     * Syncs the Opposition table with the Leads
     */
    public function syncContactsWithOppositionLists ();

    /**
     * Validates a StagingContact using exceptions and
     * opposition lists
     *
     * @param $contact
     *
     * @internal param $contacts
     * @return mixed
     */
    public function validateStagingContact ($contact);
}