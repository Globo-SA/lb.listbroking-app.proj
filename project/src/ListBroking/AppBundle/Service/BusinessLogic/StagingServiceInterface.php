<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */
namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Entity\OppositionList;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Service\Base\BaseServiceInterface;

interface StagingServiceInterface extends BaseServiceInterface
{

    /**
     * Adds a new staging contact inferring
     * the fields by the array key
     *
     * @param array $dataArray
     *
     * @return StagingContact
     */
    public function addStagingContact(array $dataArray);

    /**
     * Finds contacts that need validation and lock them
     * to the current process
     *
     * @param int $limit
     *
     * @return StagingContact[]
     */
    public function findAndLockContactsToValidate($limit = 50);

    /**
     * Imports an Opposition list by file
     *
     * @param string    $type
     * @param \PHPExcel $file
     */
    public function importOppositionList($type, \PHPExcel $file);

    /**
     * @param string $type
     * @param string $phone
     *
     * @return OppositionList
     */
    public function addPhoneToOppositionList(string $type, string $phone): OppositionList;

    /**
     * Imports contacts from a file to the staging area
     * with optional default contact information
     *
     * @param \PHPExcel $file
     * @param int       $batchSize
     * @param array     $extraFields
     *
     * @return
     */
    public function importStagingContacts(\PHPExcel $file, $batchSize, array $extraFields = []);

    /**
     * Loads validated contacts from the staging area
     * to the Lead and Contact tables
     *
     * @param StagingContact $contact
     */
    public function loadValidatedContact(StagingContact $contact);

    /**
     * Loads validated contacts from the staging area
     * to the Lead and Contact tables
     *
     * @param int $limit
     *
     * @return
     */
    public function moveInvalidContactsToDQP($limit);

    /**
     * Loads an updated contact from the staging area
     * to the Lead and Contact tables
     *
     * @param StagingContact $stagingContact
     *
     * @return mixed
     */
    public function loadUpdatedContact(StagingContact $stagingContact);

    /**
     * Syncs the Opposition table with the Leads
     */
    public function syncContactsWithOppositionLists();

    /**
     * Validates a StagingContact using exceptions and
     * opposition lists
     *
     * @param StagingContact $contact
     *
     * @internal param $contacts
     * @return mixed
     */
    public function validateStagingContact(StagingContact $contact);

    /**
     * Finds Leads with expired TYPE_INITIAL_LOCK
     *
     * @param integer $limit
     *
     * @return Lead[]
     */
    public function findLeadsWithExpiredInitialLock ($limit);
}
