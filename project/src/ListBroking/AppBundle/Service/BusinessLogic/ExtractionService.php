<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use DateTime;
use ListBroking\AppBundle\Engine\FilterEngine;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionLog;
use ListBroking\AppBundle\Enum\ExtractionFieldsEnum;
use ListBroking\AppBundle\Enum\HttpStatusCodeEnum;
use ListBroking\AppBundle\Form\FiltersType;
use ListBroking\AppBundle\Entity\RevenueFilter;
use ListBroking\AppBundle\Model\ExtractionFilter;
use ListBroking\AppBundle\Repository\CampaignRepositoryInterface;
use ListBroking\AppBundle\Repository\ExtractionRepositoryInterface;
use ListBroking\AppBundle\Repository\GenderRepositoryInterface;
use ListBroking\AppBundle\Service\Base\BaseService;
use ListBroking\AppBundle\Service\Helper\FileHandlerServiceInterface;
use ListBroking\AppBundle\Service\Helper\MessagingServiceInterface;
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
     * @var MessagingServiceInterface
     */
    private $messagingService;

    /**
     * @var ExtractionRepositoryInterface
     */
    private $extractionRepository;

    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var GenderRepositoryInterface
     */
    private $genderRepository;

    /**
     * ExtractionService constructor.
     *
     * @param RequestStack                  $requestStack
     * @param FilterEngine                  $filterEngine
     * @param MessagingServiceInterface     $messagingService
     * @param ExtractionRepositoryInterface $extractionRepository
     * @param CampaignRepositoryInterface   $campaignRepository
     * @param GenderRepositoryInterface     $genderRepository
     */
    public function __construct(
        RequestStack $requestStack,
        FilterEngine $filterEngine,
        MessagingServiceInterface $messagingService,
        ExtractionRepositoryInterface $extractionRepository,
        CampaignRepositoryInterface $campaignRepository,
        GenderRepositoryInterface $genderRepository
    ) {
        $this->request              = $requestStack->getCurrentRequest();
        $this->f_engine             = $filterEngine;
        $this->messagingService     = $messagingService;
        $this->extractionRepository = $extractionRepository;
        $this->campaignRepository   = $campaignRepository;
        $this->genderRepository     = $genderRepository;
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
        return $this->extractionRepository->cloneExtraction($extraction);
    }

    /**
     * @inheritdoc
     */
    public function findExtraction($id)
    {
        return $this->extractionRepository->find($id);
    }

    /**
     * @inheritdoc
     */
    public function findExtractionContacts(Extraction $extraction, $limit = null)
    {
        return $this->entityManager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                   ->findExtractionContacts($extraction, $limit);
    }

    /**
     * @inheritdoc
     */
    public function findExtractionSummary(Extraction $extraction)
    {
        return $this->entityManager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                   ->findExtractionSummary($extraction);
    }

    /**
     * @inheritdoc
     */
    public function generateLocks(Extraction $extraction, $lock_types)
    {
        $this->entityManager->getRepository('ListBrokingAppBundle:Lock')
                            ->generateLocks($extraction, $lock_types, $this->findConfig('lock.time'));
    }

    /**
     * @inheritdoc
     */
    public function getExtractionContactsQuery(Extraction $extraction, $fetch_mode = null)
    {
        return $this->entityManager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                   ->findExtractionContactsQuery($extraction, null, $fetch_mode);
    }

    /**
     * @inheritDoc
     */
    public function exportExtractionContacts(
        FileHandlerServiceInterface $file_service,
        Extraction $extraction,
        $template,
        $batch_size
    ) {
        $total                    = 0;
        $offset                   = 0;
        $total_contacts_to_export = $this->entityManager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                                        ->countExtractionContacts($extraction);
        $batches_to_run           = ceil($total_contacts_to_export / $batch_size);
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
                sprintf("Export Running - current_batch: %s", $i),
                ['extraction_id' => $extraction->getId()]
            );

            $extraction_contacts       = $this->entityManager->getRepository('ListBrokingAppBundle:ExtractionContact')
                                                             ->findExtractionContactsWithIdOffset(
                                                                 $extraction,
                                                                 $template['headers'],
                                                                 $batch_size,
                                                                 $offset
                                                             );
            $batch_extraction_contacts = count($extraction_contacts);

            $total += $batch_extraction_contacts;
            $this->logger->info(
                sprintf(
                    'BATCH: %s LIMIT: %s OFFSET: %s CONTACTS: %s',
                    $i,
                    $batch_size,
                    $offset,
                    $batch_extraction_contacts
                )
            );

            $file_service->writeArray($extraction_contacts, ['extraction_contact_id']);

            $last_of_batch = end($extraction_contacts);
            $offset        = $last_of_batch['extraction_contact_id'];
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
    public function handleFiltration(Extraction $extraction)
    {
        $form = $this->form_factory->createBuilder('filters', $extraction->getFilters())
                                   ->getForm();

        // Handle the filters form
        $filters_form = $form->handleRequest($this->request);
        $filters      = $filters_form->getData();

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
    public function runExtraction(Extraction $extraction)
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
    public function uploadDeduplicationsByFile(Extraction $extraction, \PHPExcel $file, $field)
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
    public function logExtractionAction(Extraction $extraction, $message)
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
     * @param int              $page
     * @param int              $limit
     *
     * @return array|null
     */
    public function getActiveCampaigns($start_date, $end_date, $page, $limit)
    {
        return $this->extractionRepository->getActiveCampaigns($start_date, $end_date, $page, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getRevenue(RevenueFilter $filter)
    {
        return $this->extractionRepository->getRevenue($filter);
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
        return $this->extractionRepository->findExtractionsByName($name);
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
     * @inheritDoc
     */
    public function createExtraction(ExtractionFilter $extractionFilter): Extraction
    {
        $campaign = $this->campaignRepository->findOneBy(['id' => $extractionFilter->getCampaignId()]);
        if ($campaign === null) {
            throw new \Exception(
                'An invalid campaign_id was provided', HttpStatusCodeEnum::HTTP_STATUS_CODE_BAD_REQUEST
            );
        }

        // create extraction
        $extraction = $this->extractionRepository->createExtraction($campaign, $extractionFilter);

        // update filter
        $filters    = $this->buildFilter($extractionFilter);
        $extraction->setFilters($filters);
        $readableFilters = FiltersType::humanizeFilters($this->entityManager, $extraction->getFilters());
        $extraction->setReadableFilters($readableFilters);

        // move status to confirmation
        $extraction->setIsAlreadyExtracted(false);
        $extraction->setStatus(Extraction::STATUS_CONFIRMATION);
        $this->updateEntity($extraction);

        // publish to start extracting
        $this->messagingService->publishMessage(
            'run_extraction',
            [
                'object_id' => $extraction->getId()
            ]
        );

        return $extraction;
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
    private function executeFilterEngine(Extraction $extraction)
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

        $query = [
            'dql' => $query->getDQL(),
            'sql' => $query->getSQL(),
        ];

        // Update Extraction
        $extraction->setStatus(Extraction::STATUS_CONFIRMATION);
        $extraction->setIsAlreadyExtracted(true);
        $extraction->setQuery(json_encode($query));

        $this->updateEntity($extraction);
    }

    /**
     * @param ExtractionFilter $requestedFilter
     *
     * @return array
     */
    private function buildFilter(ExtractionFilter $requestedFilter): array
    {
        $filterGender = [];
        foreach ($this->genderRepository->getByName($requestedFilter->getGender()) as $gender) {
            $filterGender[] = $gender->getId();
        }

        return [
            'contact:gender:boolean:required:inclusion' => $requestedFilter->getGender() !== null
                                                           || isset($requestedFilter->getFields()['gender']),
            'contact:firstname:boolean:required:inclusion' => false,
            'contact:lastname:boolean:required:inclusion' => false,
            'contact:birthdate:boolean:required:inclusion' => $requestedFilter->getMinAge() !== null
                                                              || $requestedFilter->getMaxAge() !== null
                                                              || isset($requestedFilter->getFields()[ExtractionFieldsEnum::BIRTHDATE])
                                                              || isset($requestedFilter->getFields()[ExtractionFieldsEnum::AGE]),
            'contact:address:boolean:required:inclusion' => isset($requestedFilter->getFields()[ExtractionFieldsEnum::ADDRESS]),
            'contact:postalcode1:boolean:required:inclusion' => isset($requestedFilter->getFields()[ExtractionFieldsEnum::POSTALCODE1]),
            'contact:postalcode2:boolean:required:inclusion' => isset($requestedFilter->getFields()[ExtractionFieldsEnum::POSTALCODE2]),
            'contact:gender:array:basic:inclusion' => $filterGender,
            'contact:birthdate:range:basic:inclusion' => [
                '1' => [
                    'birthdate' => sprintf(
                        '%s - %s',
                        $this->calculateFilterBirthdate(
                            $requestedFilter->getMaxAge(),
                            100
                        ),
                        $this->calculateFilterBirthdate(
                            $requestedFilter->getMinAge(),
                            18
                        )
                    ),
                ],
            ],
            'contact:birthdate:range:basic:exclusion' => ['1' => ['birthdate' => null]],
            'contact:date:range:basic:inclusion' => ['1' => ['date' => null]],
            'lead:is_mobile:choice:basic:inclusion' => 'yes',
            'contact:is_clean:choice:basic:inclusion' => 'yes',
            'lead:is_ready_to_use:choice:basic:inclusion' => 'yes',
            'lead:is_sms_ok:choice:basic:inclusion' => 'both',
            'contact:country:integer:basic:inclusion' => $this->entityManager->getRepository('ListBrokingAppBundle:Country')
                                                                             ->findOneBy(['name' => $requestedFilter->getCountry()])
                                                                             ->getId(),
            'contact:district:array:basic:inclusion' => $requestedFilter->getIncludedDistricts(),
            'contact:district:array:basic:exclusion' => $requestedFilter->getExcludedDistricts(),
            'contact:county:array:basic:inclusion' => null,
            'contact:county:array:basic:exclusion' => null,
            'contact:parish:array:basic:inclusion' => null,
            'contact:parish:array:basic:exclusion' => null,
            'contact:postalcode1:integer:basic:inclusion' => null,
            'contact:postalcode1:integer:basic:exclusion' => null,
            'contact:postalcode2:integer:basic:inclusion' => null,
            'contact:postalcode2:integer:basic:exclusion' => null,
            'contact:owner:array:basic:inclusion' => [$requestedFilter->getOwner()],
            'contact:owner:array:basic:exclusion' => null,
            'contact:source:array:basic:inclusion' => null,
            'contact:source:array:basic:exclusion' => null,
            'contact:sub_category:array:basic:inclusion' => $requestedFilter->getIncludedCategories(),
            'contact:sub_category:array:basic:exclusion' => $requestedFilter->getExcludedCategories(),
            'contact_campaign:max_times_sold:integer:not_sold_more_than_x_times_after_date:inclusion' => null,
            'contact_campaign:created_at:greater_than:not_sold_more_than_x_times_after_date:inclusion' => null,
            'lock:no_locks_lock_filter:boolean:no_locks:inclusion' => false,
            'lock:client_lock_filter:array:client_lock:inclusion' => [
                '1' => [
                    'client' => null,
                    'interval' => ''
                ]
            ],
            'lock:campaign_lock_filter:array:campaign_lock:inclusion' => [
                '1' => [
                    'campaign' => null,
                    'interval' => '',
                ],
            ],
            'lock:category_lock_filter:array:category_lock:inclusion' => [
                '1' => [
                    'category' => null,
                    'interval' => '',
                ],
            ],
            'lock:sub_category_lock_filter:array:sub_category_lock:inclusion' => [
                '1' => [
                    'sub_category' => null,
                    'interval'     => '',
                ],
            ],
        ];
    }

    /**
     * @param int|null $age
     * @param int      $defaultAge
     *
     * @return string
     */
    private function calculateFilterBirthdate(?int $age, int $defaultAge): string
    {
        $currentDate = new DateTime('now');

        $age = ($age === null)
            ? $defaultAge
            : $age;

        return $currentDate->modify(sprintf('-%s year', $age))->format('Y/m/d');
    }
}
