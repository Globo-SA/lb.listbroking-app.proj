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

class OppositionListRepository extends EntityRepository  {

    /**
     * Imports an Opposition list file by type, clears old values by default
     * @param $type
     * @param $config
     * @param $file
     * @param bool $clear_old
     * @throws \Doctrine\DBAL\DBALException
     */
    public function importOppositionListFile($type, $config, $file, $clear_old = true){

        $conn = $this->getEntityManager()->getConnection();

        if ($clear_old){
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

        $row_iterator = $file->getWorksheetIterator()->current()->getRowIterator();

        $em = $this->getEntityManager();

        $batch = 0;
        $batchSize = 1000;

        /** @var \PHPExcel_Worksheet_Row $row */
        foreach ($row_iterator as $row)
        {
            // Skip header
            if ($row->getRowIndex() == 1 && $config['has_header']){
                continue;
            }

            /** @var \PHPExcel_Cell $cell */
            foreach ($row->getCellIterator() as $cell)
            {
                $value = $cell->getValue();
                if (empty($value)){
                    continue;
                }

                // Import numbers from the correct cells
                if (in_array($cell->getColumn(), $config['phone_columns'])) {
                    $opposition = new OppositionList();
                    $opposition->setType($type);
                    $opposition->setPhone($value);
                    $em->persist($opposition);

                    $batch++;

                    if (($batch % $batchSize) === 0) {

                        $batch = 0;
                        $em->flush();
                    }
                }
            }
        }

        $em->flush();
        $em->clear();
    }

}
