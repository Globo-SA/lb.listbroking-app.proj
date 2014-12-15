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
     * Validates StagingContacts using exceptions and
     * opposition lists
     * @param $limit
     * @return mixed
     */
    public function validateStagingContacts($limit = 50)
    {
        $contacts = $this->em->getRepository('ListBrokingAppBundle:StagingContact')->findBy(array(
            'valid' => 0
        ), null, $limit);

        $this->v_engine->run($contacts);
    }

    /**
     * Enriches StagingContacts using internal and external
     * processes, if only runs on valid contacts
     * @param $limit
     * @return mixed
     */
    public function enrichStatingContacts($limit = 50)
    {
        // TODO: Implement enrichStatingContacts() method.
    }


}