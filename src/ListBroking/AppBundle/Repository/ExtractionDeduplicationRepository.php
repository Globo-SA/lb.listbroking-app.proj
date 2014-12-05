<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Repository;


use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionDeduplication;

class ExtractionDeduplicationRepository extends EntityRepository {

    /**
     * Adds multiple deduplications
     * @param $extraction Extraction
     * @param $field
     * @param $data_array
     * @param bool $merge
     * @internal param $contacts
     * @return mixed
     */
    public function uploadDeduplicationsByFile($filename, Extraction $extraction, $field, $merge = true)
    {
        $conn = $this->getEntityManager()->getConnection();

        $file = new \SplFileObject($filename);
        $file_control = $file->getCsvControl();

        $insert_sql =<<<SQL
            LOAD DATA LOCAL INFILE :filename
            INTO TABLE extraction_deduplication
            FIELDS TERMINATED BY '{$file_control[0]}'
            ENCLOSED BY '{$file_control[1]}'
            LINES TERMINATED BY '\\n'
            IGNORE 1 LINES
            ({$field})
            SET extraction_id = :extraction
SQL;
        $params = array(
            'filename' => $filename,
            'extraction' => $extraction->getId()
        );

        $conn->prepare($insert_sql)
             ->execute($params);

        $cleanup_sql = <<<SQL
            DELETE
            FROM extraction_deduplication
            WHERE IFNULL(phone, 0) = 0
            AND IFNULL(phone, 0) = 0
            AND IFNULL(email, 0) = 0
            AND IFNULL(lead_id, 0) = 0
            AND IFNULL(contact_id, 0) = 0
;
SQL;
        $conn->prepare($cleanup_sql)
             ->execute();
    }
} 