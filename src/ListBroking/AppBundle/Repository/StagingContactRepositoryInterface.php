<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\StagingContact;

interface StagingContactRepositoryInterface
{
    /**
     * Imports an Database file
     *
     * @param \PHPExcel $file
     * @param array     $extra_fields
     * @param           $batch_size
     */
    public function importStagingContactsFile (\PHPExcel $file, array $extra_fields = [], $batch_size);

    /**
     * Persists a new Staging Contact using and array
     *
     * @param $data_array
     *
     * @return \ListBroking\AppBundle\Entity\StagingContact
     */
    public function addStagingContact ($data_array): StagingContact;

    /**
     * Moves invalid contacts to the DQP table
     *
     * @param $limit
     */
    public function moveInvalidContactsToDQP ($limit);

    /**
     * Loads validated contacts from the staging area
     * to the Lead and Contact tables
     *
     * @param StagingContact $staging_contact
     * @param array          $dimensions
     */
    public function loadValidatedContact (StagingContact $staging_contact, array $dimensions);

    /**
     * Loads Updated StagingContacts to the
     * Contact table
     *
     * @param StagingContact $staging_contact
     * @param array          $dimensions
     */
    public function loadUpdatedContact (StagingContact $staging_contact, array $dimensions);

    /**
     * Finds contacts that need validation and lock them
     * to the current process
     *
     * @param int $limit
     *
     * @return StagingContact[]
     *
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function findAndLockContactsToValidate ($limit = 50);

    /**
     * @param string $email
     * @param string $phone
     *
     * @return mixed
     */
    public function deleteContactByEmailOrPhone(string $email, string $phone);
}