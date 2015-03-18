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
use ListBroking\AppBundle\Entity\Extraction;

class ExtractionRepository extends EntityRepository {

    /**
     * Associates multiple contacts to an extraction
     * @param $extraction Extraction
     * @param $contacts
     * @param $merge
     * @return mixed
     */
    public function addContacts(Extraction $extraction, $contacts, $merge)
    {
        $em = $this->getEntityManager();
        $batch = 1;
        $batchSize = 1000;
        if(!$merge){
            $extraction->getContacts()->clear();
        }
        foreach ($contacts as $contact){
            $contact = $em->getPartialReference('ListBrokingAppBundle:Contact', $contact['contact_id']);
            $extraction->addContact($contact);
            if (($batch % $batchSize) === 0) {
                $batch = 1;
                $em->flush();
            }
            $batch++;
        }
    }

    public function getExtractionSummary(Extraction $extraction){
        $qb = $this->createQueryBuilder('e')
            ->select('o.name, count(o.name) as total')
            ->join('e.contacts', 'c')
            ->join('c.owner', 'o')
            ->where('e = :extraction')
            ->setParameter('extraction', $extraction)
            ->groupBy('c.owner')
        ;

        return $qb->getQuery()->execute();
    }

} 