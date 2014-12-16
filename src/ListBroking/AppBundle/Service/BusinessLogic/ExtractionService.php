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

use Doctrine\ORM\Query;
use ListBroking\AppBundle\Engine\FilterEngine;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionDeduplicationQueue;
use ListBroking\AppBundle\Entity\ExtractionTemplate;
use ListBroking\AppBundle\Exception\InvalidExtractionException;
use ListBroking\AppBundle\PHPExcel\FileHandler;
use ListBroking\AppBundle\Service\Base\BaseService;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ExtractionService extends BaseService implements ExtractionServiceInterface {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var FilterEngine
     */
    private $f_engine;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    function __construct(RequestStack $requestStack, FilterEngine $filterEngine, \Swift_Mailer $mailer, \Twig_Environment $twig_Environment){
        $this->request = $requestStack->getCurrentRequest();
        $this->f_engine = $filterEngine;
        $this->mailer = $mailer;
        $this->twig = $twig_Environment;
    }

    /**
     * Used the LockService to compile and run the Extraction
     * @param Extraction $extraction
     * @throws \ListBroking\AppBundle\Exception\InvalidFilterObjectException
     * @return void
     */
    public function runExtraction(Extraction $extraction){

        // Don't reprocess by default
        $reprocess = false;

        // if the Extraction is closed save it and return
        if($extraction->getStatus() == Extraction::STATUS_FINAL){
            $this->updateEntity('extraction', $extraction);
            return;
        }

        // Filters Form
        $form = $this->generateForm('filters', null, $extraction->getFilters());

        // Update filters
        if ($this->request->getMethod() == 'POST')
        {
            // Handle the filters form
            $filters_form = $form->handleRequest($this->request);
            $filters = $filters_form->getData();

            // Serializes filters and compares them with a saved version
            // to check for changes on filters
            $serialized_filters = md5(serialize($filters));
            $old_serialized_filters = md5(serialize($extraction->getFilters()));
            if($serialized_filters != $old_serialized_filters){

                // Sets the new Filters and mark the Extraction to reprocess
                $extraction->setFilters($filters);
                $reprocess = true;
            }
        }

        // Reprocess leads list
        if($reprocess){
            // Runs the Filter compilation and generates the QueryBuilder
            $qb = $this->f_engine->compileFilters($extraction);

            // Add Contacts to the Extraction
            $contacts = $qb->getQuery()->execute();

            $this->em->getRepository('ListBrokingAppBundle:Extraction')->addContacts($extraction, $contacts, false);

            // Invalidate extraction contacts cache
            $cache_id = $extraction::CACHE_ID . "_{$extraction->getId()}_contacts";
            $this->dcache->delete($cache_id);

            // Change the Extraction Status back to filtration if there are no contacts
            if(count($contacts) < 0){
                $extraction->setStatus(Extraction::STATUS_FILTRATION);
            }else{
                $extraction->setStatus(Extraction::STATUS_CONFIRMATION);
            }

        }
        $this->updateEntity('extraction', $extraction);
    }

    /**
     * Gets all the contacts of a given Extraction with
     * all the dimensions eagerly loaded
     * @param Extraction $extraction
     * @return mixed
     */
    public function getExtractionContacts(Extraction $extraction){

        //TODO: Check if cache can handle array's this BIG !!!!
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
        // Get File Template
        $template = $extraction_template->getTemplate();
        if(!array_key_exists("headers", $template) || !array_key_exists("extension", $template)){
            throw new InvalidExtractionException('Headers or Extension missing on the ExtractionTemplate, in' . __CLASS__);
        }

        // Manage filename
        if(!array_key_exists("filename", $info)){

            $filename = $this->generateFilename($extraction_template->getName(), $template['extension']);
        }else{
            $filename = $this->generateFilename($info['filename']);
        }

        // Generate File
        $file_handler = new FileHandler();
        $file_handler->export($filename, $template['headers'], $contacts);

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
        $file_handler = new FileHandler();
        $obj = $file_handler->import($filename);

        return $file_handler->convertToArray($obj, false);
    }

    /**
     * Handle the uploaded file and adds it to the queue
     * @param Extraction $extraction
     * @param Form $form
     * @return ExtractionDeduplicationQueue
     */
    public function addDeduplicationFileToQueue(Extraction $extraction, Form $form){

        // Handle Form
        $data = $form->getData();
        $field = isset($data['field']) ? $data['field'] : 'lead_id';
        /** @var UploadedFile $file */
        $file = $data['upload_file'];
        $filename = $this->generateFilename($file->getClientOriginalName(), null, 'imports/');
        $file->move('imports', $filename);

        // Create Queue Entry
        $queue = new ExtractionDeduplicationQueue();
        $queue->setExtraction($extraction);
        $queue->setFilePath($filename);
        $queue->setField($field);

        $this->addEntity('extraction_deduplication_queue', $queue);

        return $queue;
    }

    /**
     * Persists Deduplications to the database, this function uses PHPExcel with APC
     * @param Extraction $extraction
     * @param string $filename
     * @param string $field
     * @param $merge
     * @return void
     */
    public function uploadDeduplicationsByFile(Extraction $extraction, $filename, $field, $merge){

        $this->em->getRepository('ListBrokingAppBundle:ExtractionDeduplication')
            ->uploadDeduplicationsByFile($filename, $extraction, $field, $merge);
    }

    /**
     * Get Deduplication Queue by Extraction
     * @param Extraction $extraction
     * @param bool $hydrate
     * @return mixed
     */
    public function getDeduplicationQueuesByExtraction(Extraction $extraction, $hydrate = true)
    {
        $entities = $this->getEntities('extraction_deduplication_queue', $hydrate);
        foreach ($entities as $key => $entity){
            if($hydrate){
                if($entity->getExtraction()->getId() != $extraction->getId()){
                 unset($entities[$key]);
                }
            }else{
                if($entity['extraction']['id'] != $extraction->getId()){
                    unset($entities[$key]);
                }
            }
        }

        return $entities;
    }

    /**
     * Removes Deduplicated Leads from an Extraction
     * using the ExtractionDeduplication Entity
     * @param Extraction $extraction
     * @return mixed
     */
    public function deduplicateExtraction(Extraction $extraction)
    {
        $this->em->getRepository('ListBrokingAppBundle:ExtractionDeduplication')->deduplicateExtraction($extraction);

        //TODO: Check if cache can handle array's this BIG !!!!
        $cache_id = $extraction::CACHE_ID . "_{$extraction->getId()}_contacts";
        $this->dcache->delete($cache_id);
    }

    /**
     * Generate locks for the contacts of a given Extraction
     * @param Extraction $extraction
     * @param $lock_types
     * @return mixed
     */
    public function generateLocks(Extraction $extraction, $lock_types){
        $this->em->getRepository('ListBrokingAppBundle:ExtractionDeduplication')->generateLocks($extraction, $lock_types, $this->getConfig('lock.time')->getValue());


        // Close extraction
        $extraction->setStatus(Extraction::STATUS_FINAL);
        $this->updateEntity('extraction', $extraction);
    }

    /**
     * Delivers the Extraction to a set of Emails
     * @param Extraction $extraction
     * @param $emails
     * @return mixed
     */
    public function deliverExtraction(Extraction $extraction, $emails)
    {
        $message = $this->mailer->createMessage()
            ->setSubject("LB Extraction - {$extraction->getName()}")
            ->setFrom('samuel.castro@adclick.com')
            ->setTo($emails)
            ->setBody(
                $this->twig->render(
                    '@ListBrokingApp/KitEmail/deliver_extraction.html.twig',
                    array()
                )
            )
            ->setContentType('text/html')
        ;
        $this->mailer->send($message);
    }

    /**
     * Generates the filename and generate a filename for it
     * @param $name
     * @param $extension
     * @param string $dir
     * @return string
     */
    private function generateFilename($name, $extension = null, $dir = 'exports/'){

        if($extension){
            $filename = $dir . uniqid() . "-{$name}-" . date('Y-m-d') . '.' . $extension;
        }else{
            $filename = $dir . uniqid() . "-{$name}";
        }

        return strtolower(preg_replace('/\s/i', '-', $filename));
    }

    //TODO: Remove this
//    /**
//     * Adds Leads to the Lead Filter of a given Extraction
//     * @param Extraction $extraction
//     * @param $data_array
//     * @param string $field
//     * @deprecated Will be removed this version !!!!!
//     */
//    public function excludeLeads(Extraction $extraction, $data_array, $field = 'lead_id'){
//
//        // Remove from filters
//        $filters = $extraction->getFilters();
//        if(!array_key_exists("lead:{$field}", $filters) || empty($filters["lead:{$field}"])){
//            $filters["lead:{$field}"] = array();
//        }else{
//            $filters["lead:{$field}"] = explode(',', $filters["lead:{$field}"]);
//        }
//
//        foreach ($data_array as $lead)
//        {
//            if(!in_array($lead, array_values($filters["lead:{$field}"]))){
//                array_push($filters["lead:{$field}"], $lead);
//            }
//
//            //TODO: Make this a bit more efficient
//            if($field = 'phone'){
//                $contacts = $this->em->getRepository('ListBrokingAppBundle:Contact')->findByLeadPhone($lead, Query::HYDRATE_ARRAY);
//            }else{
//                $contacts = $this->em->getRepository('ListBrokingAppBundle:Contact')->findBy(array("lead" => $lead));
//            }
//            foreach($contacts as $contact){
//
//                // Remove from ExtractionContacts
//                $extraction->getContacts()->removeElement($contact);
//            }
//        }
//        $filters["lead:{$field}"] = implode(',', $filters["lead:{$field}"]);
//        $extraction->setFilters($filters);
//
//        $this->em->flush();
//    }
}