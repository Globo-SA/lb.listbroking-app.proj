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
    public function uploadDeduplicationsByFile (Extraction $extraction, \PHPExcel $file, $field)
    {
        $conn = $this->getEntityManager()
                     ->getConnection()
        ;

        $row_iterator = $file->getWorksheetIterator()
                             ->current()
                             ->getRowIterator()
        ;

        $em = $this->getEntityManager();

        $inflector = new Inflector();
        $method = 'set' . $inflector->classify($field);

        $batch = 1;
        $batchSize = 5000;

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
                $deduplication = new ExtractionDeduplication();
                $deduplication->setExtraction($extraction);
                $deduplication->$method($value);

                $em->persist($deduplication);
            }

            if ( ($batch % $batchSize) === 0 )
            {

                $batch = 1;
                $em->flush();
            }
            $batch++;
        }
        $em->flush();

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
}