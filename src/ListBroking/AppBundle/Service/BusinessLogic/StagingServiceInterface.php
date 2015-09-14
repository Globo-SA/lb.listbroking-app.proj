<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */
namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Service\Base\BaseServiceInterface;

interface StagingServiceInterface extends BaseServiceInterface
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
     * with optional default contact information
     *
     * @param $file
     * @param $default_info
     *
     * @return mixed
     */
    public function importStagingContacts ($file, array $default_info = []);

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
     * Loads an updated contact from the staging area
     * to the Lead and Contact tables
     *
     * @param StagingContact $staging_contact
     *
     * @return mixed
     */
    public function loadUpdatedContact (StagingContact $staging_contact);

    /**
     * Syncs the Opposition table with the Leads
     */
    public function syncContactsWithOppositionLists ();

    /**
     * Validates a StagingContact using exceptions and
     * opposition lists
     *
     * @param StagingContact $contact
     *
     * @internal param $contacts
     * @return mixed
     */
    public function validateStagingContact (StagingContact $contact);
}