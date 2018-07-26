<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\OppositionList;

class OppositionListRepository extends EntityRepository implements OppositionListRepositoryInterface {

    /**
     *{@inheritdoc}
     */
    public function importOppositionListFile($type, $config, $file){

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

    /**
     *{@inheritdoc}
     */
    public function isPhoneInOppositionList(string $phone): bool
    {
        $opposition = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('ol')
                    ->from('ListBrokingAppBundle:OppositionList', 'ol')
                    ->where('ol.phone = :phone')
                    ->setParameter('phone', $phone)
                    ->getQuery()
                    ->getResult();

        return !empty($opposition);
    }

    /**
     * {@inheritdoc}
     */
    public function getByPhone(string $phone)
    {
        return $this->createQueryBuilder('ol')
            ->where('ol.phone = :phone')
            ->setParameter('phone', $phone)
            ->getQuery()
            ->execute();
    }
}
