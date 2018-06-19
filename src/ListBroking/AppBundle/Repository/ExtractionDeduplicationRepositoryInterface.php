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
     * @param int $contactId
     * @param int $leadId
     *
     * @return mixed
     */
    public function findByContactIdOrLeadId(int $contactId, int $leadId);
}