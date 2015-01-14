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
use ListBroking\AppBundle\Entity\ExtractionDeduplication;
use ListBroking\AppBundle\Entity\ExtractionTemplate;
use ListBroking\AppBundle\Exception\InvalidExtractionException;
use ListBroking\AppBundle\PHPExcel\FileHandler;
use ListBroking\AppBundle\Service\Base\BaseService;
use ListBroking\AppBundle\Service\Helper\AppService;
use ListBroking\TaskControllerBundle\Entity\Queue;
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

        return $this->em->getRepository('ListBrokingAppBundle:Contact')->getExtractionContacts($extraction);
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
     * @return Queue
     */
    public function addDeduplicationFileToQueue(Extraction $extraction, Form $form){

        // Handle Form
        $data = $form->getData();
        $field = isset($data['field']) ? $data['field'] : 'lead_id';
        /** @var UploadedFile $file */
        $file = $data['upload_file'];
        $filename = $this->generateFilename($file->getClientOriginalName(), null, 'imports/');
        $file->move('imports', $filename);

        $queue = new Queue();
        $queue->setType(AppService::DEDUPLICATION_QUEUE_TYPE);
        $queue->setValue1($extraction->getId());
        $queue->setValue2($filename);
        $queue->setValue3($field);

        $this->addEntity('queue', $queue);

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
     * Removes Deduplicated Leads from an Extraction
     * using the ExtractionDeduplication Entity
     * @param Extraction $extraction
     * @return mixed
     */
    public function deduplicateExtraction(Extraction $extraction)
    {
        $this->em->getRepository('ListBrokingAppBundle:ExtractionDeduplication')->deduplicateExtraction($extraction);
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
            ->setFrom($this->getConfig('system.email'))
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
}