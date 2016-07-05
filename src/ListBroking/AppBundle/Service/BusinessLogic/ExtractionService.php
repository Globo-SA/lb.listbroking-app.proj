<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\BusinessLogic;

use Doctrine\ORM\Query;
use ListBroking\AppBundle\Engine\FilterEngine;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionDeduplication;
use ListBroking\AppBundle\Entity\ExtractionLog;
use ListBroking\AppBundle\Form\FiltersType;
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

    public function __construct (RequestStack $requestStack, FilterEngine $filterEngine)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->f_engine = $filterEngine;
    }

    /**
     * @inheritDoc
     */
    public function findLastExtractionLog (Extraction $extraction, $limit)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:ExtractionLog')
                                    ->findLastExtractionLog($extraction, $limit)
            ;
    }

    /**
     * @inheritdoc
     */
    public function cloneExtraction (Extraction $extraction)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:Extraction')
                                    ->cloneExtraction($extraction)
            ;
    }

    /**
     * @inheritdoc
     */
    public function findExtraction ($id)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:Extraction')
                                    ->find($id)
            ;
    }

    /**
     * @inheritdoc
     */
    public function findExtractionContacts (Extraction $extraction, $limit = null)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                    ->findExtractionContacts($extraction, $limit)
            ;
    }

    /**
     * @inheritdoc
     */
    public function findExtractionSummary (Extraction $extraction)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                    ->findExtractionSummary($extraction)
            ;
    }

    /**
     * @inheritdoc
     */
    public function generateLocks (Extraction $extraction, $lock_types)
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:Lock')
                             ->generateLocks($extraction, $lock_types, $this->findConfig('lock.time'))
        ;
    }

    /**
     * @inheritdoc
     */
    public function getExtractionContactsQuery (Extraction $extraction, $fetch_mode = null)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                    ->findExtractionContactsQuery($extraction, null, $fetch_mode)
            ;
    }

    /**
     * @inheritdoc
     */
    public function handleFiltration (Extraction $extraction)
    {
        $form = $this->form_factory->createBuilder('filters', $extraction->getFilters())
                                   ->getForm()
        ;

        // Handle the filters form
        $filters_form = $form->handleRequest($this->request);
        $filters = $filters_form->getData();

        if ( $this->request->getMethod() === Request::METHOD_POST )
        {
            // Sets the new Filters and mark the Extraction to reprocess
            // and sets the status to confirmation
            $extraction->setFilters($filters);
            $readable_filters = FiltersType::humanizeFilters($this->entity_manager, $extraction->getFilters());
            $extraction->setReadableFilters($readable_filters);

            $extraction->setIsAlreadyExtracted(false);
            $extraction->setStatus(Extraction::STATUS_CONFIRMATION);

            $this->updateEntity($extraction);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function runExtraction (Extraction $extraction)
    {
        // if the Extraction is closed don't run
        if ( $extraction->getStatus() === Extraction::STATUS_FINAL )
        {
            return false;
        }

        $this->executeFilterEngine($extraction);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function uploadDeduplicationsByFile (Extraction $extraction, \PHPExcel $file, $field)
    {

        $batch_sizes = $this->findConfig("batch_sizes");

        $this->entity_manager->getRepository('ListBrokingAppBundle:ExtractionDeduplication')
                             ->uploadDeduplicationsByFile($extraction, $file, $field, $batch_sizes["deduplication"])
        ;
    }

    /**
     * @inheritDoc
     */
    public function logExtractionAction (Extraction $extraction, $message)
    {

        $extraction_log = new ExtractionLog();
        $extraction_log->setLog($message);

        $extraction->addExtractionLog($extraction_log);
        $this->entity_manager->persist($extraction_log);

        $this->updateEntity($extraction);

        return $extraction_log;
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
    private function executeFilterEngine (Extraction $extraction)
    {
        $batch_sizes = $this->findConfig('batch_sizes');

        $this->startStopWatch('filter_engine');

        // Runs the Filter compilation and generates the QueryBuilder
        $this->logExtractionAction($extraction, "\t ↳ Compiling filters");
        $qb = $this->f_engine->compileFilters($extraction);
        $this->logExtractionAction($extraction, sprintf("\t ↳ Compiled filters in %s milliseconds", $this->lapStopWatch('filter_engine')));

        $query = $qb->getQuery();

        // Execute Query
        $this->logExtractionAction($extraction, "\t ↳ Executing Query");
        $contacts = $query->execute();
        $this->logExtractionAction($extraction, sprintf("\t ↳ Finished Query in %s milliseconds", $this->lapStopWatch('filter_engine')));

        // Add Contacts to the Extraction
        $this->logExtractionAction($extraction, sprintf("\t ↳ Creating contacts with batch_size: %s", $batch_sizes['filter_engine']));
        $this->entity_manager->getRepository('ListBrokingAppBundle:Extraction')
                             ->addContacts($extraction, $contacts, $batch_sizes['filter_engine'])
        ;
        $this->logExtractionAction($extraction, sprintf("\t ↳ Finished creating contacts in %s milliseconds", $this->lapStopWatch('filter_engine')));

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
}