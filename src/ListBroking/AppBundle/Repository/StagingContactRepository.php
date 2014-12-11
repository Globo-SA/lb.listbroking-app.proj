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

use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\PHPExcel\FileHandler;

class StagingContactRepository extends EntityRepository {

    /**
     * Imports contacts from a file to the staging area
     * @param $filename
     * @param string $delimiter
     * @param string $encloser
     * @throws \Doctrine\DBAL\DBALException
     * @internal param $fields
     * @return mixed
     */
    public function importStagingContacts($filename, $delimiter, $encloser)
    {
//        $conn = $this->getEntityManager()->getConnection();
//
//        $file = new \SplFileObject($filename);
//
//        // Try to cleanup the fields a bit
//        $fields = array_filter(explode($delimiter, $file->current()), function($k) {
//            $k = trim($k);
//            return !empty($k);
//        });
//        $fields = implode(',', $fields);
//
//        $insert_sql =<<<SQL
//            LOAD DATA LOCAL INFILE :filename
//            INTO TABLE staging_contact
//            FIELDS TERMINATED BY '{$delimiter}'
//            ENCLOSED BY '{$encloser}'
//            LINES TERMINATED BY '\\n'
//            IGNORE 1 LINES
//            ({$fields})
//SQL;
//        $insert_sql_params = array(
//            'filename' => $filename,
//        );
//
//        $conn->prepare($insert_sql)
//            ->execute($insert_sql_params);
    }
} 