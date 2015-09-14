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
use ListBroking\AppBundle\Entity\Country;
use ListBroking\AppBundle\Entity\County;
use ListBroking\AppBundle\Entity\District;
use ListBroking\AppBundle\Entity\Gender;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Entity\Lock;
use ListBroking\AppBundle\Entity\Owner;
use ListBroking\AppBundle\Entity\Parish;
use ListBroking\AppBundle\Entity\Source;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Entity\SubCategory;
use ListBroking\AppBundle\Parser\DateTimeParser;

class StagingContactRepository extends EntityRepository
{

    /**
     * Imports an Database file
     *
     * @param \PHPExcel $file
     * @param array     $default_info
     */
    public function importStagingContactsFile (\PHPExcel $file, array $default_info)
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
                $array_data = array_replace($array_data, $default_info);
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
        $staging_contact = new StagingContact();
        foreach ( $data_array as $field => $value )
        {
            $method = 'set' . Inflector::camelize($field);
            if ( method_exists($staging_contact, $method) )
            {
                $staging_contact->$method($value);
            }
        }
        $staging_contact->setPostRequest(json_encode($data_array));

        // IF date and/or initial_lock_expiration_date are NULL
        // values today's date is used

        $date = DateTimeParser::getValidDateObject($staging_contact->getDate());
        $staging_contact->setDate($date);

        $initial_lock_expiration_date = DateTimeParser::getValidDateObject($staging_contact->getInitialLockExpirationDate());
        $staging_contact->setInitialLockExpirationDate($initial_lock_expiration_date);

        $this->getEntityManager()
             ->persist($staging_contact)
        ;

        return $staging_contact;
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
     * @param StagingContact $staging_contact
     */
    public function loadValidatedContact (StagingContact $staging_contact)
    {
        $em = $this->getEntityManager();

        //Dimension Tables
        $source = $this->findDimension('ListBrokingAppBundle:Source', $staging_contact->getSourceName());
        $owner = $this->findDimension('ListBrokingAppBundle:Owner', $staging_contact->getOwner());
        $sub_category = $this->findDimension('ListBrokingAppBundle:SubCategory', $staging_contact->getSubCategory());
        $gender = $this->findDimension('ListBrokingAppBundle:Gender', $staging_contact->getGender());
        $district = $this->findDimension('ListBrokingAppBundle:District', $staging_contact->getDistrict());
        $county = $this->findDimension('ListBrokingAppBundle:County', $staging_contact->getCounty());
        $parish = $this->findDimension('ListBrokingAppBundle:Parish', $staging_contact->getParish());
        $country = $this->findDimension('ListBrokingAppBundle:Country', $staging_contact->getCountry());

        // Fact Table
        $lead = $em->getRepository('ListBrokingAppBundle:Lead')
                   ->findOneBy(array(
                       'id' => $staging_contact->getLeadId()
                   ))
        ;

        // If the lead doesn't exist create a new one
        if ( ! $lead )
        {
            $lead = new Lead();
        }

        $lead->setPhone($staging_contact->getPhone());
        $lead->setIsMobile($staging_contact->getIsMobile());
        $lead->setInOpposition($staging_contact->getInOpposition());
        $lead->setCountry($country);
        $em->persist($lead);

        $contact = $em->getRepository('ListBrokingAppBundle:Contact')
                      ->findOneBy(array(
                          'id'   => $staging_contact->getContactId(),
                          'lead' => $lead
                      ))
        ;

        // If the contact doesn't exist create a new one
        if ( ! $contact )
        {
            $contact = new Contact();
        }

        $contact->updateContactFacts($staging_contact);
        $contact->updateContactDimensions(array(
            $lead,
            $source,
            $owner,
            $sub_category,
            $gender,
            $district,
            $county,
            $parish,
            $country
        ));

        $now = new \DateTime();
        $initial_lock_expiration_date = $staging_contact->getInitialLockExpirationDate();
        if ( $initial_lock_expiration_date > $now )
        {
            $lock = new Lock();
            $lock->setType(Lock::TYPE_NO_LOCKS);
            $lock->setExpirationDate($initial_lock_expiration_date);
            $lead->addLock($lock);
        }

        // Persist contact
        $em->persist($contact);

        // Remove StagingContact
        $em->remove($staging_contact);

        $em->flush();
    }

    /**
     * Loads Updated StagingContacts to the
     * Contact table
     *
     * @param StagingContact $staging_contact
     */
    public function loadUpdatedContact (StagingContact $staging_contact)
    {
        $em = $this->getEntityManager();
        $contact_id = $staging_contact->getContactId();
        if ( ! empty($contact_id) )
        {

            $contact = $em->getRepository('ListBrokingAppBundle:Contact')
                          ->findOneBy(array(
                              'id'       => $staging_contact->getContactId(),
                              'is_clean' => 0
                          ))
            ;
            if ( ! $contact )
            {
                return;
            }

            $contact->updateContactFacts($staging_contact);

            $contact->setIsClean(true);

            // Remove StagingContact
            $em->remove($staging_contact);

            $em->flush();
        }
    }

    /**
     * Finds contacts that need validation and lock them
     * to the current process
     *
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
        $staging_contacts = $this->createQueryBuilder('s')
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
        /** @var StagingContact $staging_contact */
        foreach ( $staging_contacts as $staging_contact )
        {
            $staging_contact->setRunning(true);
        }

        // Flush the changes
        $em->flush();

        // Commit the transaction removing the lock
        $em->commit();

        return $staging_contacts;
    }

    /**
     * Finds the facts table Dimensions by name
     *
     * @param $repo_name
     * @param $name
     *
     * @return null|object
     */
    private function findDimension ($repo_name, $name)
    {
        return $this->getEntityManager()
                    ->getRepository($repo_name)
                    ->findOneBy(array(
                        'name' => $name
                    ))
            ;
    }

} 

