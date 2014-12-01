<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use ListBroking\AppBundle\Engine\FilterEngine;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionTemplate;
use ListBroking\AppBundle\Exception\InvalidExtractionException;
use ListBroking\AppBundle\Tool\InflectorTool;
use Liuggio\ExcelBundle\Factory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;


class ExtractionService implements ExtractionServiceInterface {

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Cache
     */
    private $dcache;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var FormFactoryInterface
     */
    private $form_factory;

    /**
     * @var Factory
     */
    private $php_excel;

    /**
     * @var FilterEngine
     */
    private $f_engine;

    private $export_types;

    function __construct(EntityManager $entityManager, Cache $doctrineCache, RequestStack $requestStack, Session $session, FormFactoryInterface $formFactory, Factory $phpExcel, FilterEngine $filterEngine)
    {
        $this->em = $entityManager;
        $this->dcache = $doctrineCache;
        $this->request = $requestStack->getCurrentRequest();
        $this->session = $session;
        $this->form_factory = $formFactory;
        $this->php_excel = $phpExcel;
        $this->f_engine = $filterEngine;

        $this->export_types = array(
            'Excel5' => array('type' => 'Excel5', 'extension' => 'xls', 'label' => 'Excel File (.xls)'),
            'Excel2007' => array('type' => 'Excel2007', 'extension' => 'xlsx', 'label' => 'Excel File (.xlsx)'),
            'Excel2003XML' => array('type' => 'Excel2003XML', 'extension' => 'xml', 'label' => 'Excel File (.xml)'),
            'HTML' => array('type' => 'HTML', 'extension' => 'html', 'label' => 'HTML File (.html)'),
            'CSV' =>  array('type' => 'CSV', 'extension' => 'csv', 'label' => 'Save as a CSV file (.csv)')
        );
    }

    /**
     * Used the LockService to compile and run the Extraction
     * @param Extraction $extraction
     * @return void
     */
    public function runExtraction(Extraction $extraction){

        // Don't reprocess by default
        $reprocess = false;
        $flashes = $this->session->getFlashBag()->get('extraction');
        if(in_array('reprocess', array_values($flashes))){
            $reprocess = true;
        }

        // Change the Extraction Status to Filtering if it's on configuration
        if($extraction->getStatus() == Extraction::STATUS_CONFIGURATION){
            $extraction->setStatus(Extraction::STATUS_FILTRATION);
        }

        // Filters Form
        $form = $this->form_factory->createBuilder('filters', $extraction->getFilters())->getForm();

        // Update filters
        if ($this->request->getMethod() == 'POST')
        {
            // Handle the filters form
            $filters_form = $form->handleRequest($this->request);
            $filters = $filters_form->getData();

            // Serializes filters and compares them with a saved version
            // to check for changes on filters
            $serialized_filter = md5(serialize($filters));
            if(!in_array($serialized_filter, array_values($flashes))){

                // Sets the new Filters and mark the Extraction to reprocess
                $extraction->setFilters($filters);
                $reprocess = true;
            }

            $this->session->getFlashBag()->add('extraction', $serialized_filter);
        }

        // Reprocess leads list
        if($reprocess){

            // Runs the Filter compilation and generates the QueryBuilder
            $qb = $this->f_engine->compileFilters($extraction);

            // Add Contacts to the Extraction
            $contacts = $qb->getQuery()->execute();

            $this->em->getRepository('ListBrokingAppBundle:Extraction')->addContacts($extraction, $contacts, false);

            // Invalidate contact cache
            $cache_id = $extraction::CACHE_ID . "_{$extraction->getId()}_contacts";
            $this->dcache->delete($cache_id);

            // Change the Extraction Status to Confirmation if it's on filtration and has contacts
            if($extraction->getStatus() == Extraction::STATUS_FILTRATION && count($contacts) > 0){
                $extraction->setStatus(Extraction::STATUS_CONFIRMATION);
            }

        }

        $this->em->flush();
    }

    /**
     * Gets all the contacts of a given Extraction with
     * all the dimensions eagerly loaded
     * @param Extraction $extraction
     * @return mixed
     */
    public function getExtractionContacts(Extraction $extraction){

        $cache_id = $extraction::CACHE_ID . "_{$extraction->getId()}_contacts";
        if(!$this->dcache->contains($cache_id)){
            $contacts = $this->em->getRepository('ListBrokingAppBundle:Contact')->getExtractionContacts($extraction);
            if($contacts){
                $this->dcache->save($cache_id, $contacts);
            }
        }

        // Fetch from cache
        return $this->dcache->fetch($cache_id);
    }

    /**
     * Adds Leads to the Lead Filter of a given Extraction
     * @param Extraction $extraction
     * @param $leads_array
     */
    public function excludeLeads(Extraction $extraction, $leads_array){

        // Remove from filters
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

            //TODO: Make this a bit more efficient
            $contacts = $this->em->getRepository('ListBrokingAppBundle:Contact')->findBy(array('lead' => $lead['id']));
            foreach($contacts as $contact){

                // Remove from ExtractionContacts
                $extraction->getContacts()->removeElement($contact);
            }
        }

        $filters['lead:id'] = implode(',', $filters['lead:id']);
        $extraction->setFilters($filters);

        $this->em->flush();
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
        $template = $extraction_template->getTemplate();
        if(!array_key_exists("headers", $template) || !array_key_exists("extension", $template)){
            throw new InvalidExtractionException('Headers or Extension missing on the ExtractionTemplate, in' . __CLASS__);
        }

        if(!array_key_exists("filename", $info)){

            $filename = $this->generateFilename($extraction_template->getName(), $template['extension']);
        }else{
            $filename = $this->generateFilename($info['filename']);
        }

        $php_excel_obj = $this->php_excel->createPHPExcelObject();
        $writer = $this->php_excel->createWriter($php_excel_obj);

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

        /** @var InflectorTool $inflector */
        $inflector = new InflectorTool();
        foreach ($contacts as $contact)
        {
            $column = 'A';
            foreach ($headers as $field => $label){

                if($field == 'lead_id'){
                    $field_value = $contact->getlead()->getId();
                }elseif($field == 'contact_id'){
                    $field_value = $contact->getId();
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
     * @param string $dir
     * @return string
     */
    public function generateFilename($name, $extension = null, $dir = 'exports/'){

        if($extension){
            $filename = $dir . uniqid() . "-{$name}-" . date('Y-m-d') . '.' . $extension;
        }else{
            $filename = $dir . uniqid() . "-{$name}";
        }

        return strtolower(preg_replace('/\s/i', '-', $filename));
    }
}