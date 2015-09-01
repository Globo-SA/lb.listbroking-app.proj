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
                                    ->getExtractionContacts($extraction, $limit)
            ;
    }

    /**
     * @inheritdoc
     */
    public function findExtractionSummary (Extraction $extraction)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                    ->getExtractionSummary($extraction)
            ;
    }

    /**
     * @inheritdoc
     */
    public function generateLocks (Extraction $extraction, $lock_types)
    {
        $this->entity_manager->getRepository('ListBrokingAppBundle:ExtractionDeduplication')
                             ->generateLocks($extraction, $lock_types, $this->findConfig('lock.time'))
        ;
    }

    /**
     * @inheritdoc
     */
    public function getExtractionContactsQuery (Extraction $extraction)
    {
        return $this->entity_manager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                    ->getExtractionContactsQuery($extraction)
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
    public function uploadDeduplicationsByFile (Extraction $extraction, $filename, $field)
    {

        $this->entity_manager->getRepository('ListBrokingAppBundle:ExtractionDeduplication')
                             ->uploadDeduplicationsByFile($filename, $extraction, $field)
        ;
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
        $stopwatch = $this->startStopWatch('filter_engine');

        // Runs the Filter compilation and generates the QueryBuilder
        $this->logInfo(sprintf("\t ↳ Compiling filters for extraction_id: %s",$extraction->getId()));
        $qb = $this->f_engine->compileFilters($extraction);
        $this->logInfo(sprintf("\t ↳ Compiled filters in %s milliseconds for extraction_id: %s",$this->lapStopWatch('filter_engine'), $extraction->getId()));

        $query = $qb->getQuery();

        // Add Contacts to the Extraction
        $this->logInfo(sprintf("\t ↳ Executing Query for extraction_id: %s",$extraction->getId()));
        $contacts = $query->execute();
        $this->logInfo(sprintf("\t ↳ Finished Query in %s milliseconds for extraction_id: %s",$this->lapStopWatch('filter_engine'), $extraction->getId()));

        $this->logInfo(sprintf("\t ↳ Creating contacts for extraction_id: %s",$extraction->getId()));
        $this->entity_manager->getRepository('ListBrokingAppBundle:Extraction')
                             ->addContacts($extraction, $contacts, false)
        ;
        $this->logInfo(sprintf("\t ↳ Finished creating contacts in %s milliseconds for extraction_id: %s",$this->lapStopWatch('filter_engine'), $extraction->getId()));

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