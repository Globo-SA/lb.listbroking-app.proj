<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\Extraction;

interface ExtractionDeduplicationRepositoryInterface
{
    /**
     * Adds multiple deduplications
     *
     * @param      $extraction Extraction
     * @param      $file       \PHPExcel
     * @param      $field
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return mixed
     */
    public function uploadDeduplicationsByFile(Extraction $extraction, \PHPExcel $file, $field, $batch_size);

    /**
     * Remove deduplications from an extraction
     *
     * @param Extraction $extraction
     */
    public function removeDeduplications($extraction);

    /**
     * Cleanup records from $maxExtractionId or older.
     *
     * @param $maxExtractionId
     *
     * @return mixed
     */
    public function cleanUp($maxExtractionId);

    /**
     * Finds records by contact_id or lead_id
     *
     * @param int $contactId
     * @param int $leadId
     *
     * @return mixed
     */
    public function getByContactIdOrLeadId(int $contactId, int $leadId);

    /**
     * Finds records by phone
     *
     * @param string $phone
     *
     * @return array
     */
    public function getByPhone(string $phone);
}