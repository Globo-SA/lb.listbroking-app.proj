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
use ListBroking\AppBundle\Entity\ExtractionContact;

class ExtractionRepository extends EntityRepository
{

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
        $em = $this->getEntityManager();
        $batch = 1;
        $batchSize = 1000;
        // Remove old ExtractionContacts of current Extraction
        foreach (
            $extraction->getExtractionContacts()
                       ->getIterator() as $extraction_contact
        )
        {
            $em->remove($extraction_contact);
        }
        $em->flush();

        // Add the new Contacts
        foreach ( $contacts as $contact )
        {
            $contact = $em->getPartialReference('ListBrokingAppBundle:Contact', $contact['contact_id']);

            $extraction_contact = new ExtractionContact();
            $extraction_contact->setContact($contact);
            $extraction_contact->setExtraction($extraction);

            $extraction->addExtractionContact($extraction_contact);

            if ( ($batch % $batchSize) === 0 )
            {
                $batch = 1;
                $em->flush();
            }
            $batch++;
        }
    }
} 