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
use ListBroking\AppBundle\Entity\Lock;

class ExtractionDeduplicationRepository extends EntityRepository
{

    /**
     * Adds multiple deduplications
     *
     * @param      $filename
     * @param      $extraction Extraction
     * @param      $field
     *
     * @throws \Doctrine\DBAL\DBALException
     * @internal param $contacts
     * @return mixed
     */
    public function uploadDeduplicationsByFile ($filename, Extraction $extraction, $field)
    {
        $conn = $this->getEntityManager()
                     ->getConnection()
        ;

        $file = new \SplFileObject($filename);
        $file_control = $file->getCsvControl();

        // Removes previous deduplications for the current Extraction
        $delete_old_deduplication = <<<SQL
            DELETE
            FROM extraction_deduplication
            WHERE extraction_id = :extraction
SQL;

        $delete_old_deduplication_params = array(
            'extraction' => $extraction->getId(),
        );
        $conn->prepare($delete_old_deduplication)
             ->execute($delete_old_deduplication_params)
        ;

        // Inserts the new deduplications from the file
        $insert_sql = <<<SQL
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
            'filename'   => $filename,
            'extraction' => $extraction->getId(),
        );

        $conn->prepare($insert_sql)
             ->execute($insert_sql_params)
        ;

        // Cleanup bad deduplications
        $cleanup_sql = <<<SQL
            DELETE
            FROM extraction_deduplication
            WHERE extraction_id = :extraction
            AND  IFNULL(phone, 0) = 0
            AND IFNULL(lead_id, 0) = 0
            AND IFNULL(contact_id, 0) = 0
SQL;

        $delete_sql_params = array(
            'extraction' => $extraction->getId(),
        );
        $conn->prepare($cleanup_sql)
             ->execute($delete_sql_params)
        ;

        $find_leads_sql_params = array(
            'extraction' => $extraction->getId()
        );

        // Find leads to deduplicate (not in use for now)
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
             ->execute($find_leads_sql_params)
        ;
    }

    public function generateLocks (Extraction $extraction, $lock_types, $lock_time)
    {
        $em = $this->getEntityManager();
        $extraction_contacts = $extraction->getExtractionContacts();
        $extraction_id = $extraction->getId();

        $batch = 1;
        $batchSize = 500;
        foreach ( $lock_types as $lock_type )
        {
            /** @var ExtractionContact $extraction_contact */
            foreach ( $extraction_contacts as $extraction_contact )
            {
                $contact = $extraction_contact->getContact();

                $lock = new Lock();
                $lock->setType($lock_type);
                $lock->setExtraction($extraction);
                $lock->setLead($contact->getLead());
                $lock->setExpirationDate(new \DateTime($lock_time));
                switch ( $lock_type )
                {
                    case Lock::TYPE_CLIENT:
                        $lock->setClient($extraction->getCampaign()
                                                    ->getClient())
                        ;
                        break;
                    case Lock::TYPE_CAMPAIGN:
                        $lock->setCampaign($extraction->getCampaign());
                        break;
                    case Lock::TYPE_CATEGORY:
                        $lock->setCategory($contact->getSubCategory()
                                                   ->getCategory())
                        ;
                        break;
                    case Lock::TYPE_SUB_CATEGORY:
                        $lock->setSubCategory($contact->getSubCategory());
                        break;
                    default:
                        break;
                }
                $em->persist($lock);

                if ( ($batch % $batchSize) === 0 )
                {
                    $batch = 1;
                    $em->flush();

                    // Add Extraction to the EntityManager after it's cleared
                    $extraction = $em->getPartialReference('ListBrokingAppBundle:Extraction', $extraction_id);
                }
                $batch++;
            }
            $em->flush();
            $em->clear();

            $extraction = $em->getPartialReference('ListBrokingAppBundle:Extraction', $extraction_id);
        }
        $em->flush();
        $em->clear();
    }
}