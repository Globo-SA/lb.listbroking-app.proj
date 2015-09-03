<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Repository;

use Doctrine\Common\Util\Inflector;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Entity\Lock;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Parser\DateTimeParser;

class StagingContactRepository extends EntityRepository
{

    /**
     * Imports an Database file
     *
     * @param $file
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function importStagingContactsFile (\PHPExcel $file)
    {
        $row_iterator = $file->getWorksheetIterator()
                             ->current()
                             ->getRowIterator()
        ;

        $headers = array_keys(StagingContact::$import_template);

        $em = $this->getEntityManager();

        $batch = 1;
        $batchSize = 1000;

        /** @var \PHPExcel_Worksheet_Row $row */
        foreach ( $row_iterator as $row )
        {
            // Skip header
            if ( $row->getRowIndex() == 1 )
            {
                continue;
            }

            $array_data = array();

            /** @var  \PHPExcel_Cell $cell */

            $index = 0;
            foreach ( $row->getCellIterator() as $cell )
            {
                $value = trim($cell->getValue());

                if ( ! empty($value) )
                {
                    $array_data[$headers[$index]] = $value;
                }
                $index++;
            }

            if ( ! empty($array_data) && $row->getRowIndex() != 1 )
            {
                $this->addStagingContact($array_data);
            }

            if ( ($batch % $batchSize) === 0 )
            {

                $batch = 1;
                $em->flush();
            }
            $batch++;
        }
        $em->flush();
    }

    /**
     * Persists a new Staging Contact using and array
     *
     * @param $data_array
     *
     * @return \ListBroking\AppBundle\Entity\StagingContact
     */
    public function addStagingContact ($data_array)
    {
        $contact = new StagingContact();
        foreach ( $data_array as $field => $value )
        {
            $method = 'set' . Inflector::camelize($field);
            if ( method_exists($contact, $method) )
            {
                $contact->$method($value);
            }
        }
        $contact->setPostRequest(json_encode($data_array));

        // IF date and/or initial_lock_expiration_date are NULL
        // values today's date is used

        $date = $this->getValidDateObject($contact->getDate());
        $contact->setDate($date);

        $initial_lock_expiration_date = $this->getValidDateObject($contact->getInitialLockExpirationDate());
        $contact->setInitialLockExpirationDate($initial_lock_expiration_date);

        $this->getEntityManager()
             ->persist($contact)
        ;

        return $contact;
    }

    /**
     * Moves invalid contacts to the DQP table
     * @throws \Doctrine\DBAL\DBALException
     */
    public function moveInvalidContactsToDQP ()
    {

        $conn = $this->getEntityManager()
                     ->getConnection()
        ;
        $move_sql = <<<SQL
            INSERT INTO staging_contact_dqp
            SELECT *
            from staging_contact
            WHERE valid = 0 AND processed = 1

SQL;
        $conn->prepare($move_sql)
             ->execute()
        ;

        $del_sql = <<<SQL
            DELETE
            FROM staging_contact
            WHERE valid = 0 AND processed = 1
SQL;
        $conn->prepare($del_sql)
             ->execute()
        ;
    }

    /**
     * Loads validated contacts from the staging area
     * to the Lead and Contact tables
     *
     * @param StagingContact $s_contact
     */
    public function loadValidatedContact (StagingContact $s_contact)
    {
        $em = $this->getEntityManager();

        /** @var StagingContact $s_contact */

        //Dimension Tables
        $source = $em->getRepository('ListBrokingAppBundle:Source')
                     ->findOneBy(array(
                         'name' => $s_contact->getSourceName()
                     ))
        ;
        $owner = $em->getRepository('ListBrokingAppBundle:Owner')
                    ->findOneBy(array(
                        'name' => $s_contact->getOwner()
                    ))
        ;
        $sub_category = $em->getRepository('ListBrokingAppBundle:SubCategory')
                           ->findOneBy(array(
                               'name' => $s_contact->getSubCategory()
                           ))
        ;
        $gender = $em->getRepository('ListBrokingAppBundle:Gender')
                     ->findOneBy(array(
                         'name' => $s_contact->getGender()
                     ))
        ;
        $district = $em->getRepository('ListBrokingAppBundle:District')
                       ->findOneBy(array(
                           'name' => $s_contact->getDistrict()
                       ))
        ;
        $county = $em->getRepository('ListBrokingAppBundle:County')
                     ->findOneBy(array(
                         'name' => $s_contact->getCounty()
                     ))
        ;
        $parish = $em->getRepository('ListBrokingAppBundle:Parish')
                     ->findOneBy(array(
                         'name' => $s_contact->getParish()
                     ))
        ;
        $country = $em->getRepository('ListBrokingAppBundle:Country')
                      ->findOneBy(array(
                          'name' => $s_contact->getCountry()
                      ))
        ;

        $lead = $em->getRepository('ListBrokingAppBundle:Lead')
                   ->findOneBy(array(
                       'id' => $s_contact->getLeadId()
                   ))
        ;

        // If the lead doesn't exist create a new one
        if ( ! $lead )
        {
            $lead = new Lead();
        }

        $lead->setPhone($s_contact->getPhone());
        $lead->setIsMobile($s_contact->getIsMobile());
        $lead->setInOpposition($s_contact->getInOpposition());
        $lead->setCountry($country);
        $em->persist($lead);

        $contact = $em->getRepository('ListBrokingAppBundle:Contact')
                      ->findOneBy(array(
                          'id' => $s_contact->getContactId()
                      ))
        ;

        // If the contact doesn't exist create a new one
        if ( ! $contact )
        {
            $contact = new Contact();
        }

        $contact->setEmail($s_contact->getEmail());

        if ( $s_contact->getFirstname() )
        {
        }

        $contact->setExternalId($s_contact->getExternalId());
        $contact->setFirstname($s_contact->getFirstname());
        $contact->setLastname($s_contact->getLastname());
        $contact->setBirthdate(new \DateTime($s_contact->getBirthdate()));
        $contact->setAddress($s_contact->getAddress());
        $contact->setPostalcode1($s_contact->getPostalcode1());
        $contact->setPostalcode2($s_contact->getPostalcode2());
        $contact->setIpaddress($s_contact->getIpaddress());
        $contact->setDate($s_contact->getDate());

        $contact->setLead($lead);
        $contact->setSource($source);
        $contact->setOwner($owner);
        $contact->setSubCategory($sub_category);
        $contact->setGender($gender);
        $contact->setDistrict($district);
        $contact->setCounty($county);
        $contact->setParish($parish);
        $contact->setCountry($country);

        $contact->setValidations($s_contact->getValidations());
        $contact->setPostRequest($s_contact->getPostRequest());

        $now = new \DateTime();
        $initial_lock_expiration_date = $s_contact->getInitialLockExpirationDate();
        if ( $initial_lock_expiration_date > $now )
        {
            $lock = new Lock();
            $lock->setType(Lock::TYPE_NO_LOCKS);
            $lock->setExpirationDate($initial_lock_expiration_date);
            $lead->addLock($lock);
        }

        $em->persist($contact);

        $em->remove($s_contact);

        $em->flush();
    }

    /**
     * Finds contacts that need validation and lock them
     * to the current process
     * @param int $limit
     *
     * @return StagingContact[]
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function findAndLockContactsToValidate ($limit = 50)
    {
        $em = $this->getEntityManager();

        // Get contacts and lock the rows
        $em->beginTransaction();
        $contacts = $this->createQueryBuilder('s')
                         ->where('s.valid = :valid')
                         ->andWhere('s.running = :running')
                         ->setParameter('valid', false)
                         ->setParameter('running', false)
                         ->setMaxResults($limit)
                         ->getQuery()
                         ->setLockMode(LockMode::PESSIMISTIC_WRITE)// Don't "READ" OR "WRITE" while the lock is active
                         ->execute(null, Query::HYDRATE_OBJECT)
        ;

        // Set the contacts as Running
        /** @var StagingContact $contact */
        foreach ( $contacts as $contact )
        {
            $contact->setRunning(true);
        }

        // Flush the changes
        $em->flush();

        // Commit the transaction removing the lock
        $em->commit();

        return $contacts;
    }

    /**
     * Converts date to a valid datetime object
     * @param $date
     *
     * @return \DateTime
     */
    private function getValidDateObject ($date)
    {
        if ( ! $date || is_string($date) )
        {
            return DateTimeParser::stringToDateTime($date);
        }

        return $date;
    }
} 