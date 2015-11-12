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

class ExtractionRepository extends EntityRepository
{

    /**
     * Clones a given extraction and resets it's status
     *
     * @param Extraction $extraction
     *
     * @return Extraction
     */
    public function cloneExtraction (Extraction $extraction)
    {
        $clonedObject = clone $extraction;

        $clonedObject->setName($extraction->getName() . ' (duplicate)');
        $clonedObject->setStatus(Extraction::STATUS_FILTRATION);
        $clonedObject->getExtractionContacts()
                     ->clear()
        ;
        $clonedObject->getExtractionDeduplications()
                     ->clear()
        ;
        $clonedObject->setDeduplicationType(null);
        $clonedObject->setQuery(null);
        $clonedObject->setIsAlreadyExtracted(false);

        return $clonedObject;
    }

    /**
     * Associates multiple contacts to an extraction
     *
     * @param     $extraction Extraction
     * @param     $contacts
     * @param int $batch_size
     *
     * @return mixed
     */
    public function addContacts (Extraction $extraction, $contacts, $batch_size = 1000)
    {
        if(count($contacts) == 0)
        {
            return;
        }
        $extraction_id = $extraction->getId();

        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        // Remove old ExtractionContacts of current Extraction
        $connection->delete('extraction_contact', array('extraction_id' => $extraction_id));

        $batch = 1;

        // Add the new Contacts
        $batch_values = array();
        foreach ( $contacts as $contact )
        {
            $contact_id = $contact['contact_id'];

            $batch_values[] = sprintf('(%s,%s)', $extraction_id, $contact_id);

            if ( ($batch % $batch_size) === 0 )
            {
                $this->insertBatch($batch_values);

                // Reset Batch
                $batch_values = array();
                $batch = 1;
            }
            $batch++;
        }
        if(count($batch_values) > 0)
        {
            $this->insertBatch($batch_values);
        }
    }

    private function insertBatch ($batch_values)
    {
        $batch_string = implode(',', $batch_values);
        if(empty($batch_string))
        {
            return;
        }

        $sql = <<<SQL
                INSERT INTO extraction_contact (extraction_id, contact_id)
                VALUES {$batch_string}
SQL;
        $this->getEntityManager()
             ->getConnection()
             ->exec($sql)
        ;
    }
} 