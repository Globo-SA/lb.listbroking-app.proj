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

        if ( ! $contact->getDate() )
        {
            $contact->setDate(new \DateTime());
        }

        if ( ! $contact->getInitialLockExpirationDate() )
        {
            $contact->setInitialLockExpirationDate(new \DateTime());
        }

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

        $this->updateContact($s_contact, $contact, $lead, $source, $owner, $sub_category, $gender, $district, $county, $parish, $country);

        $now = new \DateTime();
        $initial_lock_expiration_date = $s_contact->getInitialLockExpirationDate();
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
        $em->remove($s_contact);

        $em->flush();
    }

    /**
     * Loads Updated StagingContacts to the
     * Contact table
     *
     * @param StagingContact $s_contact
     */
    public function loadUpdatedContact (StagingContact $s_contact)
    {
        $em = $this->getEntityManager();
        $contact_id = $s_contact->getContactId();
        if ( ! empty($contact_id) )
        {

            $contact = $em->getRepository('ListBrokingAppBundle:Contact')
                          ->find($s_contact->getContactId())
            ;

            $this->updateContact($s_contact, $contact);

            $contact->setIsClean(true);

            // Remove StagingContact
            $em->remove($s_contact);

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
     * Updates a contact with non-empty data
     *
     * @param StagingContact $s_contact
     * @param Contact        $contact
     * @param Lead           $lead
     * @param Source         $source
     * @param Owner          $owner
     * @param SubCategory    $sub_category
     * @param Gender         $gender
     * @param District       $district
     * @param County         $county
     * @param Parish         $parish
     * @param Country        $country
     */
    private function updateContact (
        StagingContact $s_contact,
        Contact $contact,
        Lead $lead = null,
        Source $source = null,
        Owner $owner = null,
        SubCategory $sub_category = null,
        Gender $gender = null,
        District $district = null,
        County $county = null,
        Parish $parish = null,
        Country $country = null)
    {
        $this->validateField($s_contact->getEmail(), function ($field) use ($contact)
        {
            $contact->setEmail($field);
        })
        ;
        $this->validateField($s_contact->getExternalId(), function ($field) use ($contact)
        {
            $contact->setExternalId($field);
        })
        ;
        $this->validateField($s_contact->getFirstname(), function ($field) use ($contact)
        {
            $contact->setFirstname($field);
        })
        ;
        $this->validateField($s_contact->getLastname(), function ($field) use ($contact)
        {
            $contact->setLastname($field);
        })
        ;
        $this->validateField(new \DateTime($s_contact->getBirthdate()), function ($field) use ($contact)
        {
            $contact->setBirthdate($field);
        })
        ;
        $this->validateField($s_contact->getAddress(), function ($field) use ($contact)
        {
            $contact->setAddress($field);
        })
        ;
        $this->validateField($s_contact->getPostalcode1(), function ($field) use ($contact)
        {
            $contact->setPostalcode1($field);
        })
        ;
        $this->validateField($s_contact->getPostalcode2(), function ($field) use ($contact)
        {
            $contact->setPostalcode2($field);
        })
        ;
        $this->validateField($s_contact->getIpaddress(), function ($field) use ($contact)
        {
            $contact->setIpaddress($field);
        })
        ;
        $this->validateField($s_contact->getDate(), function ($field) use ($contact)
        {
            $contact->setDate($field);
        })
        ;
        $this->validateField($s_contact->getValidations(), function ($field) use ($contact)
        {
            $contact->setValidations($field);
        })
        ;
        $this->validateField($s_contact->getPostRequest(), function ($field) use ($contact)
        {
            $contact->setPostRequest($field);
        })
        ;

        $this->validateField($lead, function ($field) use ($contact)
        {
            $contact->setLead($field);
        })
        ;

        $this->validateField($source, function ($field) use ($contact)
        {
            $contact->setSource($field);
        })
        ;

        $this->validateField($owner, function ($field) use ($contact)
        {
            $contact->setOwner($field);
        })
        ;

        $this->validateField($sub_category, function ($field) use ($contact)
        {
            $contact->setSubCategory($field);
        })
        ;

        $this->validateField($gender, function ($field) use ($contact)
        {
            $contact->setGender($field);
        })
        ;

        $this->validateField($district, function ($field) use ($contact)
        {
            $contact->setDistrict($field);
        })
        ;

        $this->validateField($county, function ($field) use ($contact)
        {
            $contact->setCounty($field);
        })
        ;

        $this->validateField($parish, function ($field) use ($contact)
        {
            $contact->setParish($field);
        })
        ;

        $this->validateField($country, function ($field) use ($contact)
        {
            $contact->setCountry($field);
        })
        ;
    }

    /**
     * Validates if a given field isn't empty and runs a callable function
     * if not empty
     *
     * @param          $field
     * @param callable $callback
     */
    private function validateField ($field, callable $callback)
    {
        if ( ! empty($field) )
        {
            $callback($field);
        }
    }
} 