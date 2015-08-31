<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionContact;

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
        $batch = 1;
        $batchSize = 500;

        // Remove old ExtractionContacts of current Extraction
        $dq = $em->createQuery('delete from ListBroking\AppBundle\Entity\ExtractionContact ec where ec.extraction = :extraction_id');
        $dq->setParameter('extraction_id', $extraction->getId());
        $dq->execute();

        // Add the new Contacts
        foreach ( $contacts as $contact )
        {
            /** @var Contact $contact */
            $contact = $em->getPartialReference('ListBrokingAppBundle:Contact', $contact['contact_id']);

            $extraction_contact = new ExtractionContact();
            $extraction_contact->setContact($contact);
            $extraction_contact->setExtraction($extraction);
            $em->persist($extraction_contact);

            if ( ($batch % $batchSize) === 0 )
            {
                $batch = 1;
                $em->flush();
                $em->clear();

                // Add Extraction to the EntityManager after it's cleared
                $extraction = $em->getPartialReference('ListBrokingAppBundle:Extraction', $extraction_id);
            }
            $batch++;
        }
        $em->flush();
        $em->clear();
    }
} 