<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Extraction;

class ExtractionDeduplicationRepository extends EntityRepository
{

    /**
     * Adds multiple deduplications
     *
     * @param      $extraction Extraction
     * @param      $file       \PHPExcel
     * @param      $field
     *
     * @throws \Doctrine\DBAL\DBALException
     * @return mixed
     */
    public function uploadDeduplicationsByFile(Extraction $extraction, \PHPExcel $file, $field, $batch_size)
    {
        $conn = $this->getEntityManager()
                     ->getConnection();

        $row_iterator = $file->getWorksheetIterator()
                             ->current()
                             ->getRowIterator();

        $deduplication_values = [];

        /** @var \PHPExcel_Worksheet_Row $row */
        foreach ($row_iterator as $row) {
            $cell_iterator = $row->getCellIterator();
            $value         = $cell_iterator->current()->getValue();
            $value         = preg_replace('/[^\+\d]/', '', $value);

            if (empty($value) || $row->getRowIndex() == 1) {
                continue;
            }

            $deduplication_values[$value] = $extraction->getId();

            if ((count($deduplication_values) % $batch_size) === 0) {
                $this->insertExtractionDeduplication($deduplication_values);

                $deduplication_values = [];

            }
        }

        if (!empty($deduplication_values)) {
            $this->insertExtractionDeduplication($deduplication_values);
        }


        $find_leads_sql_params = [
            'extraction' => $extraction->getId(),
        ];

        // Find leads to deduplicate (not in use for now)
        $find_leads_sql = <<<SQL
            UPDATE extraction_deduplication ed
            JOIN lead le ON ed.phone = le.phone
            SET ed.lead_id =
                CASE
                WHEN ed.lead_id IS NULL THEN
                le.id
                ELSE
                ed.lead_id
                END
                WHERE ed.extraction_id = :extraction
SQL;
        $conn->prepare($find_leads_sql)
             ->execute($find_leads_sql_params);
    }

    /**
     * Remove deduplications from an extraction
     *
     * @param Extraction $extraction
     */
    public function removeDeduplications($extraction)
    {
        $conn = $this->getEntityManager()
                     ->getConnection();

        $deleteExtractionSql = <<<SQL
            DELETE FROM extraction_deduplication
            WHERE extraction_id = ?
SQL;
        $conn->prepare($deleteExtractionSql)
             ->execute([$extraction->getId()]);
    }

    /**
     * Send Deduplications to the database
     *
     * @param array $deduplicationValues | array must be ('mobileNumber' => 'extractionId')
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function insertExtractionDeduplication(array $deduplicationValues)
    {
        if (empty($deduplicationValues)) {
            return;
        }

        $valuesPart = '';
        $insertSQL  = 'INSERT INTO extraction_deduplication ( extraction_id, phone ) VALUES %s ;';
        $conn       = $this->getEntityManager()->getConnection();

        foreach ($deduplicationValues as $mobile => $extractionId) {
            $valuesPart .= sprintf('(%s, %s ),', $conn->quote($extractionId), $conn->quote($mobile));
        }

        $valuesPart = rtrim($valuesPart, ',');

        $conn
            ->prepare(sprintf($insertSQL, $valuesPart))
            ->execute();
    }

    /**
     * Cleanup records from $maxExtractionId or older.
     *
     * @param $maxExtractionId
     *
     * @return mixed
     */
    public function cleanUp($maxExtractionId)
    {
        return $this->createQueryBuilder('ed')
                    ->delete('ListBrokingAppBundle:ExtractionDeduplication', 'ed')
                    ->where('ed.extraction_id <= :extraction')
                    ->setParameter('extraction', $maxExtractionId)
                    ->getQuery()
                    ->execute();
    }
}