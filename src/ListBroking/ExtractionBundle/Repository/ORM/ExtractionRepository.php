<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\ExtractionBundle\Repository\ORM;

use ListBroking\DoctrineBundle\Repository\ORM\BaseEntityRepository;
use ListBroking\ExtractionBundle\Entity\Extraction;
use ListBroking\ExtractionBundle\Repository\ExtractionRepositoryInterface;

class ExtractionRepository extends BaseEntityRepository implements ExtractionRepositoryInterface {

    /**
     * Associates multiple contacts to an extraction
     * @param $extraction Extraction
     * @param $contacts
     * @param $merge
     * @return mixed
     */
    public function addContacts($extraction, $contacts, $merge)
    {
        $batch = 0;
        $batchSize = 100;
        if(!$merge){
            $extraction->getContacts()->clear();
        }
        foreach ($contacts as $contact){
            $contact = $this->entityManager->getPartialReference('ListBrokingLeadBundle:Contact', $contact['id']);
            $extraction->addContact($contact, $merge);
            if (($batch % $batchSize) === 0) {
                $this->flush();
                $batch = 0;
            }
            $batch++;
        }
        $this->flush();
        $this->clear();
    }
}