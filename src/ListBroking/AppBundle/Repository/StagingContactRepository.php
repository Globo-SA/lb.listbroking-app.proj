<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Repository;

use Doctrine\Common\Util\Inflector;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Entity\Lock;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Entity\StagingContactDQP;
use ListBroking\AppBundle\Entity\StagingContactProcessed;
use ListBroking\AppBundle\Parser\DateTimeParser;

class StagingContactRepository extends EntityRepository
{

    /**
     * Imports an Database file
     *
     * @param \PHPExcel $file
     * @param array     $extra_fields
     * @param           $batch_size
     */
    public function importStagingContactsFile (\PHPExcel $file, array $extra_fields = [], $batch_size)
    {
        $conn = $this->getEntityManager()
                     ->getConnection();

        $row_iterator = $file->getWorksheetIterator()
                             ->current()
                             ->getRowIterator();

        $batch = 1;

        $staging_contacts = array();

        /** @var \PHPExcel_Worksheet_Row $row */
        foreach ( $row_iterator as $row )
        {
            // Skip header
            if ( $row->getRowIndex() == 1 )
            {
                continue;
            }

            $contact_data = array();

            /** @var  \PHPExcel_Cell $cell */
            foreach ( $row->getCellIterator() as $cell )
            {
                $value = trim($cell->getValue());
                $contact_data[] = $this->cleanUpValue($conn, $value);
            }

            $extra_fields['created_at'] = date('Y-m-d H:i:s');

            foreach($extra_fields as $field => $value) {
                $contact_data[] = $this->cleanUpValue($conn, $value);
            }

            $staging_contacts[] = $contact_data;

            if ( ($batch % $batch_size) === 0 )
            {
                $this->insertStagingContactBatch($conn, $staging_contacts, $extra_fields);

                $batch = 1;
                $staging_contacts = array();
            }

            $batch++;
        }

        if ( ! empty($staging_contacts))
        {
            $this->insertStagingContactBatch($conn, $staging_contacts, $extra_fields);
        }
    }

    /**
     * Persists a new Staging Contact using and array
     *
     * @param $data_array
     *
     * @return \ListBroking\AppBundle\Entity\StagingContact
     */
    public function addStagingContact ($data_array): StagingContact
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
     *
     * @param $limit
     */
    public function moveInvalidContactsToDQP ($limit)
    {
        // Only remove StagingContacts processed more than 5 minutes ago
        $updated_before = (new \DateTime('- 5 minutes'))->format('Y-m-d H:t:s');
        $entity_manager = $this->getEntityManager();

        $staging_contacts_to_move = $this->createQueryBuilder('s')
                                         ->andWhere('s.valid = :valid')
                                         ->andWhere('s.processed = :processed')
                                         ->andWhere('s.valid = :valid')
                                         ->andWhere('s.updated_at <= :updated_before')
                                         ->setParameters(array(
                                             'valid'          => 0,
                                             'processed'      => 1,
                                             'updated_before' => $updated_before
                                         ))
                                         ->setMaxResults($limit)
                                         ->getQuery()
                                         ->iterate()
        ;

        while ( ($staging_contact = $staging_contacts_to_move->next()) !== false )
        {
            $this->moveStagingContact($staging_contact[0], new StagingContactDQP());
        }

        $entity_manager->flush();
    }

    /**
     * Loads validated contacts from the staging area
     * to the Lead and Contact tables
     *
     * @param StagingContact $staging_contact
     * @param array          $dimensions
     */
    public function loadValidatedContact (StagingContact $staging_contact, array $dimensions)
    {
        $em = $this->getEntityManager();

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
            $lead->setPhone($staging_contact->getPhone());
            $lead->setIsMobile($staging_contact->getIsMobile());
            $lead->setInOpposition($staging_contact->getInOpposition());
            $lead->setCountry($dimensions['country']);
            $em->persist($lead);

        }

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
            $contact->setLead($lead);

            // Persist contact
            $em->persist($contact);

        }

        // Update contact information
        $contact->updateContactFacts($staging_contact);
        $contact->updateContactDimensions(array_values($dimensions));


        // Lock the Lead - Resting time
        $now = new \DateTime();
        $initial_lock_expiration_date = $staging_contact->getInitialLockExpirationDate();
        if ( $initial_lock_expiration_date > $now )
        {
            $lock = new Lock();
            $lock->setType(Lock::TYPE_INITIAL_LOCK);
            $lock->setLockDate(new \DateTime());
            $lock->setExpirationDate($initial_lock_expiration_date);
            $em->persist($lock);

            $lead->addLock($lock);
        }
        $lead->setIsReadyToUse(0);

        $em->flush();

        $staging_contact->setLeadId($lead->getId());
        $staging_contact->setContactId($contact->getId());

        // Move StagingContact
        $this->moveStagingContact($staging_contact, new StagingContactProcessed());

        $em->flush();

    }

    /**
     * Loads Updated StagingContacts to the
     * Contact table
     *
     * @param StagingContact $staging_cntact
     * @param array          $dimensions
     */
    public function loadUpdatedContact (StagingContact $staging_contact, array $dimensions)
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
                $this->moveStagingContact($staging_contact, new StagingContactDQP());
                $em->flush();

                return;
            }

            $contact->updateContactFacts($staging_contact);
            $contact->updateContactDimensions(array_values($dimensions));

            $contact->setIsClean(true);

            // Move StagingContact
            $this->moveStagingContact($staging_contact, new StagingContactProcessed());

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
        $entity_manager = $this->getEntityManager();

        // Get contacts and lock the rows
        $entity_manager->beginTransaction();
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
        $entity_manager->flush();

        // Commit the transaction removing the lock
        $entity_manager->commit();

        return $staging_contacts;
    }

    /**
     * @param StagingContact                            $from
     * @param StagingContactDQP|StagingContactProcessed $to
     */
    private function moveStagingContact (StagingContact $from, $to)
    {
        $entity_manager = $this->getEntityManager();

        $from_reflection = new \ReflectionObject($from);
        $new_reflection = new \ReflectionObject($to);

        $inflector = new Inflector();
        foreach ( $from_reflection->getProperties() as $property )
        {
            $property_name = $property->getName();
            if ( $new_reflection->hasProperty($property_name) && $property_name != 'updated_at' )
            {
                $get_method = 'get' . $inflector->classify($property_name);
                $set_method = 'set' . $inflector->classify($property_name);

                $to->$set_method($from->$get_method());
            }
        }

        $to->setId(null);
        $entity_manager->persist($to);

        $entity_manager->remove($from);
    }

    /**
     * Send a Batch of StagingContacts to the database
     *
     * @param Connection   $conn
     * @param array        $staging_contacts
     * @param array        $extra_fields
     */
    private function insertStagingContactBatch($conn, $staging_contacts, $extra_fields = [])
    {
        $fields = array_merge(array_keys(StagingContact::$import_template), array_keys($extra_fields));
        $sql_template =<<<SQL
                INSERT INTO staging_contact (%s) VALUES %s
SQL;

        $insert_query = sprintf($sql_template, implode(',', $fields), $this->implodeForInsertQuery($staging_contacts));

        $conn->prepare($insert_query)
             ->execute()
        ;
    }

    /**
     * Implodes and array for being used in an Insert Query
     *
     * @param $array
     *
     * @return string
     */
    private function implodeForInsertQuery($array)
    {
        $imploded = [];

        foreach ($array as $item)
        {
            $imploded[] = sprintf("(%s)", implode(',', $item));
        }

        return implode(',', $imploded);
    }

    /**
     * Clean value for DB insert
     *
     * @param Connection $connection
     * @param mixed      $value
     *
     * @return mixed
     */
    private function cleanUpValue(Connection $connection, $value)
    {
        // if value is empty, set it has null
        if ($value === '') {

            return 'NULL';
        }

        return is_numeric($value) || is_bool($value) ? $value : $connection->quote($value);
    }
}

