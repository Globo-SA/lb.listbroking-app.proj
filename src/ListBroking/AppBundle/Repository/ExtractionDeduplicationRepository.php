<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Repository;

use Doctrine\Common\Util\Inflector;
use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionContact;
use ListBroking\AppBundle\Entity\ExtractionDeduplication;
use ListBroking\AppBundle\Entity\Lock;

class ExtractionDeduplicationRepository extends EntityRepository
{

    /**
     * Adds multiple deduplications
     *
     * @param      $extraction Extraction
     * @param      $file \PHPExcel
     * @param      $field
     *
     * @throws \Doctrine\DBAL\DBALException
     * @return mixed
     */
    public function uploadDeduplicationsByFile (Extraction $extraction, \PHPExcel $file, $field, $batch_size)
    {
        $conn = $this->getEntityManager()
                     ->getConnection()
        ;

        $row_iterator = $file->getWorksheetIterator()
                             ->current()
                             ->getRowIterator()
        ;

        $batch = 1;

        $deduplication_values = array();

        /** @var \PHPExcel_Worksheet_Row $row */
        foreach ( $row_iterator as $row )
        {
            // Skip header
            if ( $row->getRowIndex() == 1 )
            {
                continue;
            }

            $cell_iterator = $row->getCellIterator();
            $value = $cell_iterator->current()->getValue();
            if(!empty($value))
            {

                $deduplication_values[] = sprintf("(%s, '%s' )", $extraction->getId() , $value);

            }
            if ( ($batch % $batch_size) === 0 )
            {

                $this->insertExtractionDeduplication($conn, $deduplication_values);

                $batch = 1;
                $deduplication_values = array();

            }

            $batch++;
        }

        if(empty($deduplication_values))
        {
            $this->insertExtractionDeduplication($conn, $deduplication_values);
        }


        $find_leads_sql_params = array(
            'extraction' => $extraction->getId()
        );

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
             ->execute($find_leads_sql_params)
        ;
    }

    /**
     * Send Deduplications to the database
     * 
     * @param $conn
     * @param $deduplication_values
     */
    private function insertExtractionDeduplication($conn, $deduplication_values)
    {
        $insert_dedup_sql = "INSERT INTO extraction_deduplication ( extraction_id, phone ) VALUES " . implode(", ", $deduplication_values);
        $conn->prepare($insert_dedup_sql)
             ->execute()
        ;
    }
}