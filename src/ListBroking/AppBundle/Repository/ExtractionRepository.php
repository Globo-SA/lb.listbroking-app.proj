<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Repository;

use Doctrine\DBAL\Connection;
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
     * @param $extraction Extraction
     * @param $contacts
     *
     * @return mixed
     */
    public function addContacts (Extraction $extraction, $contacts)
    {
        $extraction_id = $extraction->getId();

        $em = $this->getEntityManager();
        $connection = $em->getConnection();

        // Remove old ExtractionContacts of current Extraction
        $connection->delete('extraction_contact', array('extraction_id' => $extraction_id));


        // Add the new Contacts
        foreach ( $contacts as $contact )
        {
            $contact_id = $contact['contact_id'];

            $connection->transactional(function (Connection $connection) use ($extraction_id, $contact_id)
            {
                $connection->insert('extraction_contact', array(
                    'extraction_id' => $extraction_id,
                    'contact_id'    => $contact_id
                ))
                ;
            })
            ;
        }
    }
} 