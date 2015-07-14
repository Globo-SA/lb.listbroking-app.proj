<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\BusinessLogic;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use ListBroking\AppBundle\Engine\FilterEngine;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionDeduplication;
use ListBroking\AppBundle\Entity\ExtractionTemplate;
use ListBroking\AppBundle\Exception\InvalidExtractionException;
use ListBroking\AppBundle\Form\FiltersType;
use ListBroking\AppBundle\PHPExcel\FileHandler;
use ListBroking\AppBundle\Service\Base\BaseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ExtractionService extends BaseService implements ExtractionServiceInterface
{

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

    function __construct (RequestStack $requestStack, FilterEngine $filterEngine, \Swift_Mailer $mailer, \Twig_Environment $twig_Environment)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->f_engine = $filterEngine;
        $this->mailer = $mailer;
        $this->twig = $twig_Environment;
    }

    public function excludeLead (Extraction $extraction, $lead_id)
    {

        $dedup = new ExtractionDeduplication();
        $dedup->setExtraction($extraction);
        $dedup->setLeadId($lead_id);

        $extraction->addExtractionDeduplication($dedup);

        $this->em->persist($dedup);
        $this->em->flush();
    }

    /**
     * Clones a given extraction and resets it's status
     *
     * @param Extraction $extraction
     *
     * @return Extraction
     */
    public function cloneExtraction (Extraction $extraction)
    {

        /** @var Extraction $clonedObject */
        $clonedObject = clone $extraction;

        $clonedObject->setName($extraction->getName() . " (duplicate)");
        $clonedObject->setStatus(Extraction::STATUS_FILTRATION);
        $clonedObject->setContacts(new ArrayCollection());
        $clonedObject->setExtractionDeduplications(new ArrayCollection());
        $clonedObject->setDeduplicationType(null);
        $clonedObject->setQuery(null);
        $clonedObject->setIsAlreadyExtracted(false);

        return $clonedObject;
    }

    /**
     * Removes Deduplicated Leads from an Extraction
     * using the ExtractionDeduplication Entity
     *
     * @param Extraction $extraction
     *
     * @return mixed
     */
    public function deduplicateExtraction (Extraction $extraction)
    {
        $this->em->getRepository('ListBrokingAppBundle:ExtractionDeduplication')
                 ->deduplicateExtraction($extraction)
        ;
    }

    /**
     * Delivers the Extraction to a set of Emails
     *
     * @param Extraction $extraction
     * @param            $emails
     * @param            $filename
     * @param            $password
     *
     * @return mixed
     */
    public function deliverExtraction (Extraction $extraction, $emails, $filename, $password)
    {
        $message = \Swift_Message::newInstance()
                                 ->setSubject("LB Extraction - {$extraction->getName()}")
                                 ->setFrom('info@adclick.pt')
                                 ->setTo($emails)
                                 ->setBody($this->twig->render('@ListBrokingApp/KitEmail/deliver_extraction.html.twig', array('password' => $password)))
                                 ->setContentType('text/html')
                                 ->attach(\Swift_Attachment::fromPath($filename))
        ;

        return $this->mailer->send($message);
    }

    /**
     * Executes the filtering engine and adds the contacts
     * to the Extraction
     *
     * @param Extraction $extraction
     *
     * @return void
     * @throws \ListBroking\AppBundle\Exception\InvalidFilterObjectException
     */
    public function executeFilterEngine (Extraction $extraction)
    {

        // Runs the Filter compilation and generates the QueryBuilder
        $qb = $this->f_engine->compileFilters($extraction);

        $query = $qb->getQuery();

        // Add Contacts to the Extraction
        $contacts = $query->execute();

        $this->em->getRepository('ListBrokingAppBundle:Extraction')
                 ->addContacts($extraction, $contacts, false)
        ;

        $query = array(
            'dql' => $query->getDQL(),
            'sql' => $query->getSQL(),
        );

        // Update Extraction
        $extraction->setStatus(Extraction::STATUS_CONFIRMATION);
        $extraction->setIsAlreadyExtracted(true);
        $extraction->setQuery(json_encode($query));

        $this->updateEntity($extraction);
    }

    /**
     * Exports Leads to file
     *
     * @param Extraction $extraction
     * @param            $extraction_template ExtractionTemplate
     * @param array      $info
     *
     * @throws InvalidExtractionException
     * @internal param $type
     * @return mixed
     */
    public function exportExtraction (Extraction $extraction, ExtractionTemplate $extraction_template, $info = array())
    {
        $file_handler = new FileHandler();

        // Get File Template
        $template = json_decode($extraction_template->getTemplate(), true);
        if ( ! array_key_exists("headers", $template) )
        {
            throw new InvalidExtractionException('Headers missing on the ExtractionTemplate, in' . __CLASS__);
        }

        // Manage and dirfilename
        $local_filename = $file_handler->generateFilename($extraction->getName(), FileHandler::$export_types[$template['extension']]['extension']);
        if ( array_key_exists("filename", $info) )
        {
            $local_filename = $file_handler->generateFilename($info['filename'], FileHandler::$export_types[$template['extension']]['extension']);
        }
        $filename = $this->getRootDir() . '/../web/exports/' . $local_filename;

        // Get the Extraction Contacts Query
        $query = $this->em->getRepository('ListBrokingAppBundle:ExtractionContact')
                          ->getExtractionContactsQuery($extraction)
        ;

        // Generate File
        $file_handler->export($filename, $template['headers'], $template['extension'], $query);

        // Zip file and password
        $password = $this->generatePassword(10);

        $zipped_filename = $file_handler->generateFilename($extraction->getName(), 'zip');
        $zipped_filename = $this->getRootDir() . '/../web/exports/' . $zipped_filename;

        // Php isn't used because password protection on zip files is a php >= 5.6 feature
        exec(sprintf("zip -j --password %s %s %s", $password, $zipped_filename, $filename));

        // Remove the original file
        unlink($filename);

        return array($zipped_filename, $password);
    }

    /**
     * Finds all the contacts of a given Extraction with
     * all the dimensions eagerly loaded
     *
     * @param Extraction $extraction
     * @param            $limit
     *
     * @return mixed
     */
    public function findExtractionContacts (Extraction $extraction, $limit = null)
    {
        return $this->em->getRepository('ListBrokingAppBundle:ExtractionContact')
                        ->getExtractionContacts($extraction, $limit)
            ;
    }

    /**
     * Finds the ExtractionSummary
     * @param Extraction $extraction
     *
     * @return mixed
     */
    public function findExtractionSummary (Extraction $extraction)
    {
        return $this->em->getRepository('ListBrokingAppBundle:ExtractionContact')
                        ->getExtractionSummary($extraction)
            ;
    }

    /**
     * Generate locks for the contacts of a given Extraction
     *
     * @param Extraction $extraction
     * @param            $lock_types
     *
     * @return mixed
     */
    public function generateLocks (Extraction $extraction, $lock_types)
    {
        $this->em->getRepository('ListBrokingAppBundle:ExtractionDeduplication')
                 ->generateLocks($extraction, $lock_types, $this->getConfig('lock.time'))
        ;

        // Close extraction
        $extraction->setStatus(Extraction::STATUS_FINAL);
        $this->updateEntity($extraction);
    }

    /**
     * Handles Extraction Filtration
     *  . Saves new Filters
     *  . Marks Extraction to be Extracted
     *  . Sets the Extraction Status to CONFIRMATION
     *
     * @param Extraction $extraction
     *
     * @return bool Returns true if the extraction is ready to be processed by a consumer
     */
    public function handleFiltration (Extraction $extraction)
    {
        // Filters Form
        $form = $this->generateForm('filters', null, $extraction->getFilters());

        // Handle the filters form
        $filters_form = $form->handleRequest($this->request);
        $filters = $filters_form->getData();

        if ( $this->request->getMethod() == Request::METHOD_POST )
        {

            // Sets the new Filters and mark the Extraction to reprocess
            // and sets the status to confirmation
            $extraction->setFilters($filters);
            $readable_filters = FiltersType::humanizeFilters($this->em, $extraction->getFilters());
            $extraction->setReadableFilters($readable_filters);

            $extraction->setIsAlreadyExtracted(false);
            $extraction->setStatus(Extraction::STATUS_CONFIRMATION);

            $this->updateEntity($extraction);

            return true;
        }

        return false;
    }

//    /**
//     * Used to import a file with Leads
//     *
//     * @param $filename
//     *
//     * @internal param $filename
//     * @return mixed
//     */
//    public function importExtraction ($filename)
//    {
//        $file_handler = new FileHandler();
//        $obj = $file_handler->import($filename);
//
//        return $file_handler->convertToArray($obj, false);
//    }

    /**
     * Used the LockService to compile and run the Extraction
     *
     * @param Extraction $extraction
     *
     * @throws \ListBroking\AppBundle\Exception\InvalidFilterObjectException
     * @return boolean
     */
    public function runExtraction (Extraction $extraction)
    {
        // if the Extraction is closed don't run
        if ( $extraction->getStatus() == Extraction::STATUS_FINAL )
        {
            return false;
        }

        $this->executeFilterEngine($extraction);

        return true;
    }

    /**
     * Persists Deduplications to the database, this function uses PHPExcel with APC
     *
     * @param Extraction $extraction
     * @param string     $filename
     * @param string     $field
     *
     * @return void
     */
    public function uploadDeduplicationsByFile (Extraction $extraction, $filename, $field)
    {

        $this->em->getRepository('ListBrokingAppBundle:ExtractionDeduplication')
                 ->uploadDeduplicationsByFile($filename, $extraction, $field)
        ;
    }

    private function generatePassword ($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ( $i = 0, $result = ''; $i < $length; $i++ )
        {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }
}