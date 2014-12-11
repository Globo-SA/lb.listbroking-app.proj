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
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionDeduplication;
use ListBroking\AppBundle\Entity\Lock;

class ExtractionDeduplicationRepository extends EntityRepository {

    /**
     * Adds multiple deduplications
     * @param $extraction Extraction
     * @param $field
     * @param bool $merge
     * @internal param $contacts
     * @return mixed
     */
    public function uploadDeduplicationsByFile($filename, Extraction $extraction, $field, $merge = true)
    {
        $conn = $this->getEntityManager()->getConnection();

        $file = new \SplFileObject($filename);
        $file_control = $file->getCsvControl();

        $insert_sql =<<<SQL
            LOAD DATA LOCAL INFILE :filename
            INTO TABLE extraction_deduplication
            FIELDS TERMINATED BY '{$file_control[0]}'
            ENCLOSED BY '{$file_control[1]}'
            LINES TERMINATED BY '\\n'
            IGNORE 1 LINES
            ({$field})
            SET extraction_id = :extraction
SQL;
        $insert_sql_params = array(
            'filename' => $filename,
            'extraction' => $extraction->getId()
        );

        $conn->prepare($insert_sql)
             ->execute($insert_sql_params);

        $cleanup_sql = <<<SQL
            DELETE
            FROM extraction_deduplication
            WHERE IFNULL(phone, 0) = 0
            AND IFNULL(lead_id, 0) = 0
            AND IFNULL(contact_id, 0) = 0
SQL;
        $conn->prepare($cleanup_sql)
             ->execute();

        $find_leads_sql_params = array(
            'extraction' => $extraction->getId()
        );

        $find_leads_sql = <<<SQL
            UPDATE extraction_deduplication ed
            JOIN lead le ON ed.phone = le.phone
            SET ed.lead_id =
                CASE
                WHEN ed.lead_id IS NULL THEN
                le.id
                ELSE
                ed.lead_id
                END
                WHERE ed.extraction_id = :extraction
SQL;
        $conn->prepare($find_leads_sql)
             ->execute($find_leads_sql_params);
    }

    /**
     * Removes Deduplicated Leads from an Extraction
     * @param Extraction $extraction
     */
    public function deduplicateExtraction(Extraction $extraction){
        $conn = $this->getEntityManager()->getConnection();

        $dedup_sql = <<<SQL
            DELETE extractions_contacts
            FROM extractions_contacts
            JOIN contact ON contact.id = extractions_contacts.contact_id
            JOIN lead ON contact.lead_id = lead.id
            JOIN extraction_deduplication ON extraction_deduplication.phone = lead.phone
            WHERE extractions_contacts.extraction_id = :extraction
SQL;
        $params = array(
            'extraction' => $extraction->getId(),
        );

        $conn->prepare($dedup_sql)
            ->execute($params);
    }

    public function generateLocks(Extraction $extraction, $lock_types, $lock_time){

        $em = $this->getEntityManager();
        foreach ($lock_types as $lock_type)
        {
            $batch = 1;
            $batchSize = 1000;
            /** @var Contact $contact */
            foreach ($extraction->getContacts() as $contact)
            {
                $lock = new Lock();
                $lock->setType($lock_type);

                switch($lock_type){
                    case Lock::TYPE_CLIENT:
                        $lock->setClient($extraction->getCampaign()->getClient());
                        break;
                    case Lock::TYPE_CAMPAIGN:
                        $lock->setCampaign($extraction->getCampaign());
                        break;
                    case Lock::TYPE_CATEGORY:
                        $lock->setCategory($contact->getSubCategory()->getCategory());
                        break;
                    case Lock::TYPE_SUB_CATEGORY:
                        $lock->setSubCategory($contact->getSubCategory());
                        break;
                    default:
                        break;
                }

                $lock->setLead($contact->getLead());
                $lock->setExpirationDate(new \DateTime($lock_time));
                $em->persist($lock);

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