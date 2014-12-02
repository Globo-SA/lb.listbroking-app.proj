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
    public function addDeduplications(Extraction $extraction, $field, $data_array, $merge = true)
    {
        $em = $this->getEntityManager();

        $inflector = new Inflector();
        $method = 'set' . $inflector->classify($field);

        $batch = 1;
        $batchSize = 1000;
        if(!$merge){
            $extraction->getExtractionDeduplications()->clear();
        }
        foreach ($data_array as $data){

            $deduplication = new ExtractionDeduplication();
            $deduplication->setExtraction($extraction);
            $deduplication->$method($data);
            $em->persist($deduplication);

            if (($batch % $batchSize) === 0) {
                $em->flush();

                $batch = 1;
            }
            $batch++;
        }
        $em->flush();
        $em->clear();
    }
} 