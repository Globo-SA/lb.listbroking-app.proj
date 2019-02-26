<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Engine\FilterEngine;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionLog;
use ListBroking\AppBundle\Form\FiltersType;
use ListBroking\AppBundle\Entity\RevenueFilter;
use ListBroking\AppBundle\Repository\ExtractionRepository;
use ListBroking\AppBundle\Service\Base\BaseService;
use ListBroking\AppBundle\Service\Helper\FileHandlerServiceInterface;
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
     * ExtractionService constructor.
     *
     * @param RequestStack $requestStack
     * @param FilterEngine $filterEngine
     */
    public function __construct(RequestStack $requestStack, FilterEngine $filterEngine)
    {
        $this->request  = $requestStack->getCurrentRequest();
        $this->f_engine = $filterEngine;
    }

    /**
     * @inheritDoc
     */
    public function findLastExtractionLog(Extraction $extraction, $limit)
    {
        return $this->entityManager->getRepository('ListBrokingAppBundle:ExtractionLog')
                                   ->findLastExtractionLog($extraction, $limit);
    }

    /**
     * @inheritdoc
     */
    public function cloneExtraction(Extraction $extraction)
    {
        return $this->entityManager->getRepository('ListBrokingAppBundle:Extraction')
                                   ->cloneExtraction($extraction);
    }

    /**
     * @inheritdoc
     */
    public function findExtraction($id)
    {
        return $this->entityManager->getRepository('ListBrokingAppBundle:Extraction')
                                   ->find($id);
    }

    /**
     * @inheritdoc
     */
    public function findExtractionContacts (Extraction $extraction, $limit = null)
    {
        return $this->entityManager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                   ->findExtractionContacts($extraction, $limit);
    }

    /**
     * @inheritdoc
     */
    public function findExtractionSummary (Extraction $extraction)
    {
        return $this->entityManager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                   ->findExtractionSummary($extraction);
    }

    /**
     * @inheritdoc
     */
    public function generateLocks (Extraction $extraction, $lock_types)
    {
        $this->entityManager->getRepository('ListBrokingAppBundle:Lock')
                            ->generateLocks($extraction, $lock_types, $this->findConfig('lock.time'));
    }

    /**
     * @inheritdoc
     */
    public function getExtractionContactsQuery (Extraction $extraction, $fetch_mode = null)
    {
        return $this->entityManager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                   ->findExtractionContactsQuery($extraction, null, $fetch_mode);
    }

    /**
     * @inheritDoc
     */
    public function exportExtractionContacts(FileHandlerServiceInterface $file_service, Extraction $extraction, $template, $batch_size)
    {
        $total = 0;
        $offset = 0;
        $total_contacts_to_export = $this->entityManager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                                        ->countExtractionContacts($extraction);
        $batches_to_run = ceil($total_contacts_to_export / $batch_size);
        $this->logger->info(
            sprintf(
                "Export starts - total_contacts_to_export: %s, batches_to_run: %s",
                $total_contacts_to_export,
                $batches_to_run
            ),
            ['extraction_id' => $extraction->getId()]
        );

        $file_service->createFileWriter($extraction->getName(), $template['extension']);
        $file_service->openWriter();

        for ($i = 1; $i <= $batches_to_run; $i++) {
            $this->logger->info(
                sprintf("Export Running - current_batch: %s", $i), ['extraction_id' => $extraction->getId()]
            );

            $extraction_contacts = $this->entityManager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                                       ->findExtractionContactsWithIdOffset($extraction, $template['headers'], $batch_size, $offset);
            $batch_extraction_contacts = count($extraction_contacts);

            $total += $batch_extraction_contacts;
            $this->logger->info(
                sprintf('BATCH: %s LIMIT: %s OFFSET: %s CONTACTS: %s', $i, $batch_size, $offset, $batch_extraction_contacts)
            );

            $file_service->writeArray($extraction_contacts, ['extraction_contact_id']);

            $last_of_batch = end($extraction_contacts);
            $offset = $last_of_batch['extraction_contact_id'];
        }

        $export_info = $file_service->closeWriter();

        $this->logger->info(
            sprintf("Total contacts exported: %s", $total),
            ['extraction_id' => $extraction->getId()]
        );

        return $export_info;
    }

    /**
     * @inheritdoc
     */
    public function handleFiltration (Extraction $extraction)
    {
        $form = $this->form_factory->createBuilder('filters', $extraction->getFilters())
                                   ->getForm();

        // Handle the filters form
        $filters_form = $form->handleRequest($this->request);
        $filters = $filters_form->getData();

        if ($this->request->getMethod() === Request::METHOD_POST) {
            // Sets the new Filters and mark the Extraction to reprocess
            // and sets the status to confirmation
            $extraction->setFilters($filters);
            $readable_filters = FiltersType::humanizeFilters($this->entityManager, $extraction->getFilters());
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
        if ($extraction->isFinished()) {

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

        $this->entityManager->getRepository('ListBrokingAppBundle:ExtractionDeduplication')
                            ->uploadDeduplicationsByFile($extraction, $file, $field, $batch_sizes["deduplication"]);
    }

    /**
     * @inheritdoc
     */
    public function removeDeduplications(Extraction $extraction)
    {
        $this->entityManager->getRepository('ListBrokingAppBundle:ExtractionDeduplication')
                            ->removeDeduplications($extraction);
    }

    /**
     * @inheritDoc
     */
    public function logExtractionAction (Extraction $extraction, $message)
    {
        $extraction_log = new ExtractionLog();
        $extraction_log->setLog($message);

        $extraction->addExtractionLog($extraction_log);
        $this->entityManager->persist($extraction_log);

        $this->updateEntity($extraction);

        return $extraction_log;
    }

    /**
     * @param \DateTime|string $start_date
     * @param \DateTime|string $end_date
     * @param int $page
     * @param int $limit
     *
     * @return array|null
     */
    public function getActiveCampaigns($start_date, $end_date, $page, $limit)
    {
        $extractionRepository = $this->entityManager->getRepository('ListBrokingAppBundle:Extraction');
        return $extractionRepository->getActiveCampaigns($start_date, $end_date, $page, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getRevenue(RevenueFilter $filter)
    {
        /** @var ExtractionRepository $extractionRepository */
        $extractionRepository = $this->entityManager->getRepository('ListBrokingAppBundle:Extraction');

        return $extractionRepository->getRevenue($filter);
    }

    /**
     * Return all extractions
     *
     * @param string $name
     *
     * @return array
     */
    public function findExtractionsByName(string $name)
    {
        $extractionRepository = $this->entityManager->getRepository('ListBrokingAppBundle:Extraction');

        return $extractionRepository->findExtractionsByName($name);
    }

    /**
     * @param Extraction $extraction
     *
     * @return mixed
     */
    public function generateContactCampaignHistory(Extraction $extraction)
    {
        $this->logger->info(
            sprintf(
                'Generating Contact Campaign History for: Extraction %s | Campaign %s',
                $extraction->getId(),
                $extraction->getCampaign()->getId()
            )
        );

        $this->entityManager->getRepository('ListBrokingAppBundle:ContactCampaign')->generateHistory(
            $extraction->getId()
        );
    }

    /**
     * Executes the filtering engine and adds the contacts
     * to the Extraction
     *
     * @param Extraction $extraction
     *
     * @return void
     * @throws \ListBroking\AppBundle\Exception\InvalidFilterObjectException
     * @throws \ListBroking\AppBundle\Exception\InvalidFilterTypeException
     */
    private function executeFilterEngine (Extraction $extraction)
    {
        $batch_sizes = $this->findConfig('batch_sizes');

        $this->startStopWatch('filter_engine');

        // Runs the Filter compilation and generates the QueryBuilder
        $this->logger->info('Compiling filters', ['extraction_id' => $extraction->getId()]);

        $qb = $this->f_engine->compileFilters($extraction);

        $this->logger->info(
            sprintf('Compiled filters in %s milliseconds', $this->lapStopWatch('filter_engine')),
            ['extraction_id' => $extraction->getId()]
        );

        $query = $qb->getQuery();

        // Execute Query
        $this->logger->info('Executing Query', ['extraction_id' => $extraction->getId(), 'sql' => $query->getSQL()]);
        $contacts = $query->execute();
        $this->logger->info(
            sprintf('Finished Query in %s milliseconds', $this->lapStopWatch('filter_engine')),
            ['extraction_id' => $extraction->getId()]
        );

        $this->logger->info(
            sprintf('Obtained %d contacts', count($contacts)),
            ['extraction_id' => $extraction->getId()]
        );

        // Add Contacts to the Extraction
        $this->logger->info(
            sprintf('Creating contacts with batch_size: %s', $batch_sizes['filter_engine']),
            ['extraction_id' => $extraction->getId()]
        );
        $this->entityManager->getRepository('ListBrokingAppBundle:Extraction')
                            ->addContacts($extraction, $contacts, $batch_sizes['filter_engine']);
        $this->logger->info(
            sprintf(
                'Finished adding contacts to extraction in %s milliseconds',
                $this->lapStopWatch('filter_engine')
            ),
            ['extraction_id' => $extraction->getId()]
        );

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
