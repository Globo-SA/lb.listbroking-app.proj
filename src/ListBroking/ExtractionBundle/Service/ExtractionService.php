<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\ExtractionBundle\Service;


use Adclick\CacheBundle\Manager\CacheManagerInterface;
use ListBroking\CoreBundle\Service\BaseService;
use ListBroking\ExtractionBundle\Entity\Extraction;
use ListBroking\ExtractionBundle\Entity\ExtractionTemplate;
use ListBroking\ExtractionBundle\Exception\InvalidExtractionException;
use ListBroking\ExtractionBundle\Repository\ORM\ExtractionRepository;
use ListBroking\ExtractionBundle\Repository\ORM\ExtractionTemplateRepository;
use ListBroking\LeadBundle\Service\ContactDetailsService;
use ListBroking\LeadBundle\Service\LeadService;
use ListBroking\LockBundle\Service\LockService;
use Liuggio\ExcelBundle\Factory;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExtractionService extends BaseService implements ExtractionServiceInterface {

    /**
     * @var LockService
     */
    private $l_service;

    /** @var  LeadService */
    private $le_service;

    private $extraction_repo;
    private $extraction_template_repo;
    private $export_types;

    private $phpexcel;

    const EXTRACTION_LIST = 'extraction_list';
    const EXTRACTION_SCOPE = 'extraction';

    const EXTRACTION_TEMPLATE_LIST = 'extraction_template_list';
    const EXTRACTION_TEMPLATE_SCOPE = 'extraction_template';

    function __construct(
        LockService $l_service,
        LeadService $le_service,
        CacheManagerInterface $cache,
        ValidatorInterface $validator,
        ExtractionRepository $extraction_repo,
        ExtractionTemplateRepository $extraction_template_repo,
        Factory $phpexcel
    )
    {
        parent::__construct($cache, $validator);
        $this->le_service = $le_service;
        $this->extraction_repo = $extraction_repo;
        $this->extraction_template_repo = $extraction_template_repo;
        $this->phpexcel = $phpexcel;

        $this->export_types = array(
            'Excel5' => array('type' => 'Excel5', 'extension' => 'xls', 'label' => 'Excel File (.xls)'),
            'Excel2007' => array('type' => 'Excel2007', 'extension' => 'xlsx', 'label' => 'Excel File (.xlsx)'),
            'Excel2003XML' => array('type' => 'Excel2003XML', 'extension' => 'xml', 'label' => 'Excel File (.xml)'),
            'HTML' => array('type' => 'HTML', 'extension' => 'html', 'label' => 'HTML File (.html)'),
            'CSV' =>  array('type' => 'CSV', 'extension' => 'csv', 'label' => 'Save as a CSV file (.csv)')
        );
        $this->l_service = $l_service;
    }

    /**
     * Gets list of extractions
     * @param bool $only_active
     * @return mixed
     */
    public function getExtractionList($only_active = false){
        return $this->getList(self::EXTRACTION_LIST, self::EXTRACTION_SCOPE, $this->extraction_repo, $only_active);

    }

    /**
     * Gets a single extraction
     * @param $id
     * @param $hydrate
     * @return mixed
     */
    public function getExtraction($id, $hydrate = false){
        $extraction= $this->get(self::EXTRACTION_LIST, self::EXTRACTION_SCOPE, $this->extraction_repo, $id, $hydrate);
        if (!$extraction)
        {
            throw new HttpException(404, "Extraction not found!", null, array(), 404);
        }

        return $extraction;
    }

    /**
     * Adds a single extraction
     * @param $extraction
     * @return mixed
     */
    public function addExtraction($extraction){
        $this->add(self::EXTRACTION_LIST, self::EXTRACTION_SCOPE, $this->extraction_repo, $extraction);
        return $this;
    }

    /**
     * Removes a single extraction
     * @param $id
     * @return mixed
     */
    public function removeExtraction($id){
        $this->remove(self::EXTRACTION_LIST, self::EXTRACTION_SCOPE, $this->extraction_repo, $id);
        return $this;
    }

    /**
     * Updates a single country
     * @param $extraction
     * @return mixed
     */
    public function updateExtraction($extraction){
        $this->update(self::EXTRACTION_LIST, self::EXTRACTION_SCOPE, $this->extraction_repo, $extraction);
        return $this;
    }

    /**
     * Gets list of extraction_templates
     * @param bool $only_active
     * @return mixed
     */
    public function getExtractionTemplateList($only_active = false){
        return $this->getList(self::EXTRACTION_TEMPLATE_LIST, self::EXTRACTION_TEMPLATE_SCOPE, $this->extraction_template_repo, $only_active);

    }

    /**
     * Gets a single extraction_template
     * @param $id
     * @param $hydrate
     * @return mixed
     */
    public function getExtractionTemplate($id, $hydrate = false){
        return $this->get(self::EXTRACTION_TEMPLATE_LIST, self::EXTRACTION_TEMPLATE_SCOPE, $this->extraction_template_repo, $id, $hydrate);

    }

    /**
     * Adds a single extraction_template
     * @param $extraction_template
     * @return mixed
     */
    public function addExtractionTemplate($extraction_template){
        $this->add(self::EXTRACTION_TEMPLATE_LIST, self::EXTRACTION_TEMPLATE_SCOPE, $this->extraction_template_repo, $extraction_template);
        return $this;
    }

    /**
     * Removes a single extraction_template
     * @param $id
     * @return mixed
     */
    public function removeExtractionTemplate($id){
        $this->remove(self::EXTRACTION_TEMPLATE_LIST, self::EXTRACTION_TEMPLATE_SCOPE, $this->extraction_template_repo, $id);
        return $this;
    }

    /**
     * Updates a single extraction_template
     * @param $extraction_template
     * @return mixed
     */
    public function updateExtractionTemplate($extraction_template){
        $this->update(self::EXTRACTION_TEMPLATE_LIST, self::EXTRACTION_TEMPLATE_SCOPE, $this->extraction_template_repo, $extraction_template);
        return $this;
    }

    /**
     * Used the LockService to compile and run the Extraction
     * @param Extraction $extraction
     * @return mixed
     */
    public function runExtraction(Extraction $extraction){

        // Start the filtering Engine and query the DB
        $engine = $this->l_service->startEngine();
        $qb = $engine->compileFilters($engine->prepareFilters($extraction->getFilters()), $extraction->getQuantity());

        // Add Contacts to the Extraction
        $contacts = $qb->getQuery()->execute();
        $extraction = $this->addExtractionContacts($extraction, $contacts);

        // Change the Extraction Status to Confirmation if it's on filtration and has contacts
        if($extraction->getStatus() == Extraction::STATUS_FILTRATION && count($contacts) > 0){
            $extraction->setStatus(Extraction::STATUS_CONFIRMATION);
            $this->updateExtraction($extraction);
        }

        return $contacts;
    }

    /**
     * Gets all the contacts of a given Extraction with
     * all the dimensions eagerly loaded
     * @param Extraction $extraction
     * @return mixed
     */
    public function getExtractionContacts(Extraction $extraction){
        return $this->le_service->getExtractionContacts($extraction);
    }

    /**
     * Set the Extraction filters
     * @param Extraction $extraction
     * @param $filters
     * @internal param $id
     * @internal param $lock_filters
     * @internal param $contact_filters
     * @return mixed
     */
    public function setExtractionFilters(Extraction $extraction, $filters)
    {
        // Update extraction with new filters
        $extraction->setFilters($filters);
        $this->updateExtraction($extraction);
    }

    /**
     * Associates an array of contacts to an extraction
     * If merge = false old contacts will be removed
     * @param $extraction Extraction
     * @param $contacts
     * @param bool $merge
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function addExtractionContacts($extraction, $contacts, $merge = false){

        $this->extraction_repo->addContacts($extraction, $contacts, $merge);
        return  $this->extraction_repo->findOneById($extraction->getId(),true);
    }

    /**
     * Adds Leads to the Lead Filter of a given Extraction
     * @param Extraction $extraction
     * @param $leads_array
     */
    public function excludeLeads(Extraction $extraction, $leads_array){

        $filters = $extraction->getFilters();
        if(!array_key_exists('lead:id', $filters) || empty($filters['lead:id'])){
            $filters['lead:id'] = array();
        }else{
            $filters['lead:id'] = explode(',', $filters['lead:id']);
        }
        foreach ($leads_array as $lead)
        {
            if(!in_array($lead['id'], array_values($filters['lead:id']))){
                array_push($filters['lead:id'], $lead['id']);
            }
        }
        $filters['lead:id'] = implode(',', $filters['lead:id']);

        $extraction->setFilters($filters);
        $this->updateExtraction($extraction);
    }

    /**
     * Gets all the Existing Export Types
     * @return array
     */
    public function getExportTypes()
    {
        return $this->export_types;
    }

    /**
     * Exports Leads using a given type
     * @param $extraction_template ExtractionTemplate
     * @param $contacts
     * @param array $info
     * @throws InvalidExtractionException
     * @internal param $type
     * @return mixed
     */
    public function exportExtraction(ExtractionTemplate $extraction_template, $contacts, $info = array())
    {
        $template = $extraction_template['template'];
        if(!array_key_exists("headers", $template) || !array_key_exists("extension", $template)){
            throw new InvalidExtractionException('Headers or Extension missing on the ExtractionTemplate, in' . __CLASS__);
        }

        if(!array_key_exists("filename", $info)){

            $filename = $this->generateFilename($extraction_template['name'], $template['extension']);
        }else{
            $filename = $info['filename'];
        }

        $php_excel_obj = $this->phpexcel->createPHPExcelObject();
        $writer = $this->phpexcel->createWriter($php_excel_obj);

        // Set File Properties
        if(!empty($info) && is_array($info)){
            $properties = $php_excel_obj->getProperties();
            if(array_key_exists('modified_by', $info)){
                $properties->setLastModifiedBy($info['modified_by']);
            }
            if(array_key_exists('title', $info)){
                $properties->setTitle($info['title']);
            }
            if(array_key_exists('subject', $info)){
                $properties->setSubject($info['subject']);
            }
            if(array_key_exists('description', $info)){
                $properties->setDescription($info['description']);
            }
            if(array_key_exists('keywords', $info)){
                $properties->setKeywords($info['keywords']);
            }
            if(array_key_exists('category', $info)){
                $properties->setCategory($info['category']);
            }
            if(array_key_exists('sheet_title', $info)){
                $php_excel_obj->getActiveSheet()->setTitle($info['sheet_title']);
            }
        }
        $active_sheet = $php_excel_obj->getActiveSheet();

        $header_column = 'A';
        $headers = $template['headers'];
        foreach ($headers as $field => $label){
            $active_sheet->setCellValue("{$header_column}1", $label);
            $header_column++;
        }

        $line = 2;

        /** @var \ListBroking\DoctrineBundle\Tool\InflectorTool $inflector */
        $inflector = $field_value = $this->extraction_repo->getInflector();
        foreach ($contacts as $contact)
        {
            $column = 'A';
            foreach ($headers as $field => $label){

                if($field == 'id'){
                    $field_value = $contact->getlead()->getId();
                }elseif($field == 'phone'){
                    $field_value = $contact->getlead()->getPhone();
                }else{
                    $method = 'get' . $inflector->camelize($field);
                    $field_value = $contact->$method();
                    if(is_object($field_value) && !($field_value instanceof \DateTime)){
                        $field_value = $field_value->__toString();
                    }
                }
                if($field_value instanceof \DateTime){
                    $field_value = $field_value->format('Y-m-d');
                }

                if(!empty($field_value)){
                    $active_sheet->setCellValue("{$column}{$line}", $field_value);
                }
                $column++;
            }
            $line++;
        }

        $writer->save($filename);

        return $filename;
    }

    /**
     * Used to import a file with Leads
     * @param $filename
     * @internal param $filename
     * @return mixed
     */
    public function importExtraction($filename)
    {
        $active = $php_excel_obj = \PHPExcel_IOFactory::load($filename)->getActiveSheet();
        $last_row = $active->getHighestRow();
        $last_column = $active->getHighestColumn();

        $headers = array();
        $lead_array = array();
        for($i = 1; $i <= $last_row; $i++){

            $j = 0;
            $column = 'A';
            while($column <= $last_column){
                if($i == 1){
                    $headers[] = strtolower($active->getCell("{$column}1")->getValue());
                }
                else{
                    $lead_array[$i-1][$headers[$j]] = $active->getCell("{$column}{$i}")->getValue();
                }
                $column++;
                $j++;
            }
        }
        return $lead_array;
    }

    /**
     * Generates the filename and generate a filename for it
     * @param $name
     * @param $extension
     * @return string
     */
    private function generateFilename($name, $extension){

        $filename = 'exports/' . $name . '-' . date('Y-m-d') . '.' . $extension;

        return strtolower(preg_replace('/\s/i', '-', $filename));
    }


} 