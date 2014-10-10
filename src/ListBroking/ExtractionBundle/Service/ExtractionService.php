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
use Liuggio\ExcelBundle\Factory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExtractionService extends BaseService implements ExtractionServiceInterface {

    private $extraction_repo;
    private $extraction_template_repo;
    private $export_types;

    private $phpexcel;

    const EXTRACTION_LIST = 'extraction_list';
    const EXTRACTION_SCOPE = 'extraction';

    const EXTRACTION_TEMPLATE_LIST = 'extraction_template_list';
    const EXTRACTION_TEMPLATE_SCOPE = 'extraction_template';

    function __construct(
        CacheManagerInterface $cache,
        ValidatorInterface $validator,
        ExtractionRepository$extraction_repo,
        ExtractionTemplateRepository $extraction_template_repo,
        Factory $phpexcel
    )
    {
        parent::__construct($cache, $validator);
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
        return $this->get(self::EXTRACTION_LIST, self::EXTRACTION_SCOPE, $this->extraction_repo, $id, $hydrate);

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
     * Set the Extraction filters
     * @param $id
     * @param $filters
     * @internal param $lock_filters
     * @internal param $contact_filters
     * @return mixed
     */
    public function setExtractionFilters($id, $filters)
    {
        $extraction = $this->getExtraction($id, true);
        $extraction->setFilters($filters);

        $this->updateExtraction($extraction);
    }

    /**
     * Adds a Lock Filter to an Extraction
     * @param $id
     * @param $type
     * @param $new_filters
     * @internal param $filters
     * @internal param $filter
     * @return mixed
     */
    public function addExtractionLockFilters($id, $type, $new_filters)
    {
        $this->setFilters($id, 'lock_filters', $type, $new_filters);
    }

    /**
     * Adds a Contact Filter to an Extraction
     * @param $id
     * @param $type
     * @param $new_filters
     * @internal param $filters
     * @internal param $filter
     * @return mixed
     */
    public function addExtractionContactFilters($id, $type, $new_filters)
    {
            $this->setFilters($id, 'contact_filters', $type, $new_filters);
    }

    private function setFilters($id, $filter_type,  $type, $new_filters){

        /** @var Extraction $extraction */
        $extraction = $this->getExtraction($id, true);
        $filters = $extraction->getFilters();

        $filters[$filter_type][$type]['filters'] = array_merge(
            $filters[$filter_type][$type]['filters'],
            $new_filters
        );
        $extraction->setFilters($filters);
        $this->updateExtraction($extraction);
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
     * @return array
     */
    public function getExportTypes()
    {
        return $this->export_types;
    }

    /**
     * Exports Leads using a given type
     * @param $extraction_template ExtractionTemplate
     * @param $leads_array
     * @param $type
     * @param array $info
     * @throws InvalidExtractionException
     * @return mixed
     */
    public function exportExtraction($extraction_template, $leads_array, $type, $info = array())
    {
        if(!array_key_exists("extension", $type) || !array_key_exists("label", $type)){
            throw new InvalidExtractionException('Wrong Extraction type, in' . __CLASS__);
        }

        $template = $extraction_template['template'];
        if(!array_key_exists("headers", $template)){
            throw new InvalidExtractionException('Headers missing on the ExtractionTemplate, in' . __CLASS__);
        }

        $filename = $this->generateFilename($extraction_template['name'], $type['extension']);

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
        foreach ($leads_array as $contact)
        {
            $column = 'A';
            foreach ($headers as $field => $label){

                    $field_value = $contact[$field];
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
                    $headers[] = $active->getCell("{$column}1")->getValue();
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