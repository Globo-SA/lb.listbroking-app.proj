<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Repository;


use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\OppositionList;
use ListBroking\AppBundle\PHPExcel\FileHandler;

class OppositionListRepository extends EntityRepository  {

    /**
     * Imports an Opposition list file by type, clears old values by default
     * @param $type
     * @param $config
     * @param $filename
     * @param bool $clear_old
     * @throws \Doctrine\DBAL\DBALException
     */
    public function importOppositionListFile($type, $config, $filename, $clear_old = true){

        $conn = $this->getEntityManager()->getConnection();

        if($clear_old){
            $cleanup_sql = <<<SQL
            DELETE
            FROM opposition_list
            WHERE type = :type
SQL;
            $clear_sql_params = array(
                'type' => $type
            );
            $conn->prepare($cleanup_sql)
                ->execute($clear_sql_params);
        }

        $file_handler = new FileHandler();
        $obj = $file_handler->import($filename);
        $row_iterator = $obj->getWorksheetIterator()->current()->getRowIterator();

        $em = $this->getEntityManager();

        /** @var \PHPExcel_Worksheet_Row $row */
        foreach ($row_iterator as $row)
        {
            // Skip header
            if($row->getRowIndex() == 1 && $config['has_header']){
                continue;
            }

            $batch = 1;
            $batchSize = 1000;

            /** @var \PHPExcel_Cell $cell */
            foreach ($row->getCellIterator() as $cell)
            {
                $value = $cell->getValue();
                if(empty($value)){
                    continue;
                }

                // Import numbers from the correct cells
                if(in_array($cell->getColumn(), $config['phone_columns'])){
                    $opposition = new OppositionList();
                    $opposition->setType($type);
                    $opposition->setPhone($value);

                    $em->persist($opposition);
                }

                if (($batch % $batchSize) === 0) {

                    $batch = 1;
                    $em->flush();
                }
                $batch++;
            }
            $em->flush();
        }
        $em->clear();
    }

} 