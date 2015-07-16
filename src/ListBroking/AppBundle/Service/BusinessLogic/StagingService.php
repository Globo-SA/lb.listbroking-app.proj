<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\BusinessLogic;

use Doctrine\Common\Util\Inflector;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Query;
use ListBroking\AppBundle\Engine\ValidatorEngine;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Service\Base\BaseService;
use ListBroking\AppBundle\Service\Helper\AppService;
use ListBroking\TaskControllerBundle\Entity\Queue;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StagingService extends BaseService implements StagingServiceInterface
{

    /**
     * @var ValidatorEngine
     */
    protected $v_engine;

    function __construct (ValidatorEngine $v_engine)
    {
        $this->v_engine = $v_engine;
    }

    /**
     * Adds a new staging contact inferring
     * the fields by the array key
     *
     * @param $data_array
     *
     * @return mixed
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
        $this->entity_manager->persist($contact);
        $this->entity_manager->flush();
        $this->entity_manager->clear();
    }

    /**
     * Handle the uploaded StagingContacts file and adds it to the queue
     *
     * @param Form $form
     *
     * @throws \Exception
     * @return Queue
     */
    public function addStagingContactsFileToQueue (Form $form)
    {

        // Handle Form
        $data = $form->getData();

        if ( empty($data['upload_file']) )
        {
            throw new \Exception('Invalid or empty filename');
        }

        $file_handler = new FileHandler();

        /** @var UploadedFile $file */
        $file = $data['upload_file'];
        $filename = $file_handler->generateFilename($file->getClientOriginalName(), null, 'imports/');
        $file->move('imports', $filename);

        $queue = new Queue();
        $queue->setType(AppService::CONTACT_IMPORT_QUEUE_TYPE);
        $queue->setValue1($filename);

        $this->addEntity('queue', $queue);

        return $queue;
    }

    /**
     * Gets contacts that need validation and lock them
     * to the current process
     *
     * @param int $limit
     *
     * @return mixed
     */
    public function findContactsToValidateAndLock ($limit = 50)
    {
        // Get contacts and lock the rows
        $this->entity_manager->beginTransaction();
        $contacts = $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                             ->createQueryBuilder('s')
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
        $this->entity_manager->flush();

        // Commit the transaction removing the lock
        $this->entity_manager->commit();

        return $contacts;
    }

    /**
     * Used to generate a template file for importing staging contacts
     * @return string
     */
    public function getStagingContactImportTemplate ()
    {
        $filehandler = new FileHandler();

        $filename = $filehandler->generateFilename('staging_contact_import_template');
        $filehandler->export($filename, array(
            'external_id',
            'phone',
            'email',
            'firstname',
            'lastname',
            'birthdate',
            'address',
            'postalcode1',
            'postalcode2',
            'ipaddress',
            'gender',
            'district',
            'county',
            'parish',
            'country',
            'owner',
            'source_name',
            'source_external_id',
            'source_country',
            'sub_category',
            'date'
        ), array())
        ;

        return $filename;
    }

    /**
     * Imports an Opposition list by file
     *
     * @param $type
     * @param $file
     * @param $clear_old
     */
    public function importOppostionList ($type, $file, $clear_old)
    {

        $config = json_decode($this->findConfig('opposition_list.config'), true);

        $this->entity_manager->getRepository('ListBrokingAppBundle:OppositionList')
                 ->importOppositionListFile($type, $config[$type], $file, $clear_old)
        ;
    }

    public function isOppositionListImporting()
    {
        return $this->doctrine_cache->contains('importing_opposition_list');
    }

    public function startOppostionListImporting(){
        $this->doctrine_cache->save('importing_opposition_list', true);
    }

    public function endOppositionListImporting(){
        $this->doctrine_cache->delete('importing_opposition_list');
    }

    /**
     * Imports contacts from a file to the staging area
     *
     * @param $file
     *
     * @return mixed
     */
    public function importStagingContacts ($file)
    {
        $headers = array();
        $row_iterator = $file->getActiveSheet()
                                     ->getRowIterator()
        ;
        foreach ( $row_iterator as $row )
        {
            $array_data = array();
            foreach ( $row->getCellIterator() as $key => $cell )
            {
                $value = trim($cell->getValue());
                if ( ! empty($value) )
                {
                    if ( $row->getRowIndex() == 1 )
                    {
                        $headers[] = $value;
                    }
                    else
                    {
                        if ( array_key_exists($key, $headers) )
                        {
                            $array_data[$headers[$key]] = $value;
                        }
                    }
                }
            }
            if ( ! empty($array_data) && $row->getRowIndex() != 1 )
            {
                $this->addStagingContact($array_data);
            }
        }
    }

    /**
     * Loads validated contacts from the staging area
     * to the Lead and Contact tables
     *
     * @param StagingContact $contact
     */
    public function loadValidatedContact (StagingContact $contact)
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                 ->loadValidatedContact($contact)
        ;
    }

    /**
     * Loads validated contacts from the staging area
     * to the Lead and Contact tables
     */
    public function moveInvalidContactsToDQP ()
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:StagingContact')
                 ->moveInvalidContactsToDQP()
        ;
    }

    /**
     * Syncs the Opposition table with the Leads
     */
    public function syncContactsWithOppositionLists ()
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:Lead')
                 ->syncContactsWithOppositionLists()
        ;
    }

    /**
     * Validates a StagingContact using exceptions and
     * opposition lists
     *
     * @param $contact
     *
     * @internal param $contacts
     * @return mixed
     */
    public function validateStagingContact ($contact)
    {
        $this->v_engine->run($contact);
    }
}