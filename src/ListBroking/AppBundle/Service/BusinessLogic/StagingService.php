<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\BusinessLogic;


use Doctrine\Common\Util\Inflector;
use ListBroking\AppBundle\Engine\ValidatorEngine;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\PHPExcel\FileHandler;
use ListBroking\AppBundle\Service\Base\BaseService;
use ListBroking\AppBundle\Service\Helper\AppService;
use ListBroking\TaskControllerBundle\Entity\Queue;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StagingService extends BaseService implements StagingServiceInterface {

    /**
     * @var ValidatorEngine
     */
    protected  $v_engine;

    function __construct(ValidatorEngine $v_engine)
    {
        $this->v_engine = $v_engine;
    }

    /**
     * Adds a new staging contact inferring
     * the fields by the array key
     * @param $data_array
     * @return mixed
     */
    public function addStagingContact($data_array)
    {
        $contact = new StagingContact();
        foreach ($data_array as $field => $value)
        {
            $method = 'set' . Inflector::camelize($field);
            if(method_exists($contact, $method)){
                $contact->$method($value);
            }
        }
        $contact->setPostRequest(json_encode($data_array));
        $this->em->persist($contact);
        $this->em->flush();
        $this->em->clear();
    }

    /**
     * Imports contacts from a file to the staging area
     * @param $filename
     * @return mixed
     */
    public function importStagingContacts($filename)
    {
        $headers = array();
        $file_handler = new FileHandler();
        $row_iterator = $file_handler->import($filename)->getActiveSheet()->getRowIterator();
        foreach ($row_iterator as $row)
        {
            $array_data = array();
            foreach ($row->getCellIterator() as $key => $cell)
            {
                $value = trim($cell->getValue());
                if(!empty($value)){
                    if($row->getRowIndex() == 1){
                        $headers[] = $value;
                    }else {
                        if(array_key_exists($key, $headers)){
                            $array_data[$headers[$key]] = $value;
                        }
                    }
                }
            }
            if(!empty($array_data) && $row->getRowIndex() != 1){
                $this->addStagingContact($array_data);
            }
        }
    }

    /**
     * Gets contacts that need validation
     * @param int $limit
     * @return mixed
     */
    public function findContactsToValidate($limit = 50)
    {
        return $contacts = $this->em->getRepository('ListBrokingAppBundle:StagingContact')->findBy(array(
            'valid' => 0
        ), null, $limit);
    }

    /**
     * Validates a StagingContact using exceptions and
     * opposition lists
     * @param $contact
     * @internal param $contacts
     * @return mixed
     */
    public function validateStagingContact($contact)
    {
        $this->v_engine->run($contact);
    }

    /**
     * Loads validated contacts from the staging area
     * to the Lead and Contact tables
     */
    public function moveInvalidContactsToDQP(){
        $this->em->getRepository('ListBrokingAppBundle:StagingContact')->moveInvalidContactsToDQP();
    }

    /**
     * Loads validated contacts from the staging area
     * to the Lead and Contact tables
     * @param StagingContact $contact
     */
    public function loadValidatedContact(StagingContact $contact){
        $this->em->getRepository('ListBrokingAppBundle:StagingContact')->loadValidatedContact($contact);
    }

    /**
     * Handle the uploaded file and adds it to the queue
     * @param Form $form
     * @throws \Exception
     * @return Queue
     */
    public function addOppositionListFileToQueue(Form $form){

        // Handle Form
        $data = $form->getData();

        if(empty($data['type'])){
            throw new \Exception('Invalid or empty type');
        }
        if(empty($data['upload_file'])){
            throw new \Exception('Invalid or empty filename');
        }

        /** @var UploadedFile $file */
        $file = $data['upload_file'];
        $filename = $this->generateFilename($file->getClientOriginalName(), null, 'imports/');
        $file->move('imports', $filename);

        $queue = new Queue();
        $queue->setType(AppService::OPPOSITION_LIST_QUEUE_TYPE);
        $queue->setValue1($data['type']);
        $queue->setValue2($filename);
        $queue->setValue3($data['clear_old']);

        $this->addEntity('queue', $queue);

        return $queue;
    }

    /**
     * Handle the uploaded StagingContacts file and adds it to the queue
     * @param Form $form
     * @throws \Exception
     * @return Queue
     */
    public function addStagingContactsFileToQueue(Form $form){

        // Handle Form
        $data = $form->getData();

        if(empty($data['upload_file'])){
            throw new \Exception('Invalid or empty filename');
        }

        /** @var UploadedFile $file */
        $file = $data['upload_file'];
        $filename = $this->generateFilename($file->getClientOriginalName(), null, 'imports/');
        $file->move('imports', $filename);

        $queue = new Queue();
        $queue->setType(AppService::CONTACT_IMPORT_QUEUE_TYPE);
        $queue->setValue1($filename);

        $this->addEntity('queue', $queue);

        return $queue;
    }

    /**
     * Imports an Opposition list by file
     * @param $type
     * @param $filename
     * @param $clear_old
     */
    public function importOppostionList($type, $filename, $clear_old){

        $config = json_decode($this->getConfig('opposition_list.config')->getValue(),true);
        $this->em->getRepository('ListBrokingAppBundle:OppositionList')->importOppositionListFile($type, $config[$type], $filename, $clear_old);
    }

    /**
     * Syncs the Opposition table with the Leads
     */
    public function syncContactsWithOppositionLists(){
        $this->em->getRepository('ListBrokingAppBundle:Lead')->syncContactsWithOppositionLists();
    }

    /**
     * Used to generate a template file for importing staging contacts
     * @return string
     */
    public function getStagingContactImportTemplate(){
        $filehandler = new FileHandler();

        $filename = $this->generateFilename('staging_contact_import_template');
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
        ), array());

        return $filename;
    }
}