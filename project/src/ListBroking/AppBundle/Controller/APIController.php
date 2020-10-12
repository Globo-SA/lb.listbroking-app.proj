<?php

namespace ListBroking\AppBundle\Controller;

use DateTime;
use Doctrine\DBAL\DBALException;
use ListBroking\AppBundle\Builder\Entity\RevenueFilterEntityBuilder;
use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Enum\HttpStatusCodeEnum;
use ListBroking\AppBundle\Exception\Validation\LeadValidationException;
use ListBroking\AppBundle\Model\AudiencesFilter;
use ListBroking\AppBundle\Model\ExtractionFilter;
use ListBroking\AppBundle\Service\Authentication\FosUserAuthenticationServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\CampaignServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ClientNotificationServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ContactObfuscationServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ExtractionContactServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ExtractionServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\LeadServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\StagingServiceInterface;
use ListBroking\AppBundle\Service\Helper\StatisticsServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class APIController
 */
class APIController extends Controller
{
    const CONTACT_EMAIL_ERASURE_MESSAGE    = 'Contact has been erased';
    const CONTACTS_NOT_FOUND_MESSAGE       = 'Contacts not found. Nothing to do';
    const COULD_NOT_ERASE_CONTACT_MESSAGE  = 'An error occurred while erasing contact.';
    const CAMPAIGN_CREATED_MESSAGE         = 'Campaign was created';
    const CAMPAIGN_NOT_CREATED_MESSAGE     = 'There was an error creating the campaign. Please contact support';
    const EXTRACTION_CREATED_MESSAGE       = 'Extraction was created';
    const EXTRACTION_NOT_CREATED_MESSAGE   = 'There was an error creating the extraction. Please contact support';
    const INVALID_FILTER_MESSAGE           = 'The requested filter is invalid';
    const AUDIENCE_DETAIL_OBTAINED_MESSAGE = 'Audience detail obtained';
    const EXTRACTION_CLOSED_MESSAGE        = 'Extraction with id %s was closed';
    const EXTRACTION_NOT_CLOSED_MESSAGE    = 'There was an error closing the extraction. Please contact support';
    const EXTRACTION_CONTACTS_OK_MESSAGE   = 'Extraction contacts obtained';
    const EXTRACTION_CONTACTS_NOK_MESSAGE  = 'There was an error getting the extraction contacts. Please contact support';

    private const EXTRACTION_CONTACTS_PER_PAGE = 100;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var StagingServiceInterface $stagingService
     */
    private $stagingService;

    /**
     * @var ExtractionServiceInterface $extractionService
     */
    private $extractionService;

    /**
     * @var ExtractionContactServiceInterface
     */
    private $extractionContactService;

    /**
     * @var FosUserAuthenticationServiceInterface $fosUserAuthenticationService
     */
    private $fosUserAuthenticationService;

    /**
     * @var ContactObfuscationServiceInterface
     */
    private $contactObfuscationService;

    /**
     * @var LeadServiceInterface
     */
    private $leadService;

    /**
     * @var ClientNotificationServiceInterface
     */
    private $clientNotificationService;

    /**
     * @var CampaignServiceInterface
     */
    private $campaignService;

    /**
     * @var StatisticsServiceInterface
     */
    private $statisticsService;

    /**
     * APIController constructor.
     *
     * @param LoggerInterface                       $logger
     * @param StagingServiceInterface               $stagingService
     * @param ExtractionServiceInterface            $extractionService
     * @param ExtractionContactServiceInterface     $extractionContactService
     * @param FosUserAuthenticationServiceInterface $fosUserAuthenticationService
     * @param ContactObfuscationServiceInterface    $contactObfuscationService
     * @param LeadServiceInterface                  $leadService
     * @param ClientNotificationServiceInterface    $clientNotificationService
     * @param CampaignServiceInterface              $campaignService
     * @param StatisticsServiceInterface            $statisticsService
     */
    public function __construct(
        LoggerInterface $logger,
        StagingServiceInterface $stagingService,
        ExtractionServiceInterface $extractionService,
        ExtractionContactServiceInterface $extractionContactService,
        FosUserAuthenticationServiceInterface $fosUserAuthenticationService,
        ContactObfuscationServiceInterface $contactObfuscationService,
        LeadServiceInterface $leadService,
        ClientNotificationServiceInterface $clientNotificationService,
        CampaignServiceInterface $campaignService,
        StatisticsServiceInterface $statisticsService
    ) {
        $this->logger                       = $logger;
        $this->stagingService               = $stagingService;
        $this->extractionService            = $extractionService;
        $this->extractionContactService     = $extractionContactService;
        $this->fosUserAuthenticationService = $fosUserAuthenticationService;
        $this->contactObfuscationService    = $contactObfuscationService;
        $this->leadService                  = $leadService;
        $this->clientNotificationService    = $clientNotificationService;
        $this->campaignService              = $campaignService;
        $this->statisticsService            = $statisticsService;
    }


    /**
     * Dumb Action that just saves the lead for the ETL process
     *
     * @param Request $request
     *
     * @throws LeadValidationException
     *
     */
    public function createStagingLeadAction(Request $request)
    {
        try {
            $this->fosUserAuthenticationService->checkCredentials($request);

            $lead = $request->get('lead');

            if (!$lead) {
                throw new LeadValidationException('Lead is empty');
            }

            $this->stagingService->addStagingContact($lead);
            $this->stagingService->flushAll();
            $this->stagingService->clearEntityManager();

            return $this->createJsonResponse('Lead added');
        } catch (\Exception $e) {
            return $this->createJsonResponse($e->getMessage(), [], HttpStatusCodeEnum::HTTP_STATUS_CODE_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getActiveCampaignsAction(Request $request)
    {
        try {
            $this->fosUserAuthenticationService->checkCredentials($request);

            $end_date   = $request->get('end_date');
            $start_date = $request->get('start_date');
            if ($start_date == null) {
                if ($end_date != null) {
                    return $this->createJsonResponse(
                        'end date can\'t be defined without a start date',
                        [],
                        HttpStatusCodeEnum::HTTP_STATUS_CODE_BAD_REQUEST
                    );
                }
                $start_date = date('Y-m-1');
            }
            if ($end_date == null) {
                $end_date = date('Y-m-t');
            }
            $data = $this->extractionService->getActiveCampaigns(
                $start_date,
                $end_date,
                $request->get('page', 1),
                $request->get(
                    'page_size',
                    HttpStatusCodeEnum::HTTP_STATUS_CODE_INTERNAL_SERVER_ERROR
                )
            );
            if ($data == null) {
                return $this->createJsonResponse('invalid request', [], HttpStatusCodeEnum::HTTP_STATUS_CODE_BAD_REQUEST);
            }

            return $this->createJsonResponse('List of active campaigns obtained', $data);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf(
                    'API Active Campaigns error: %s start_date: %s end_date: %s trace: %s',
                    $e->getMessage(),
                    $request->get('start_date'),
                    $request->get('end_date'),
                    $e->getTraceAsString()
                )
            );

            return $this->createJsonResponse($e->getMessage(), [], HttpStatusCodeEnum::HTTP_STATUS_CODE_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Requests the extractions revenue.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getExtractionsRevenueAction(Request $request)
    {
        try {
            $this->fosUserAuthenticationService->checkCredentials($request);

            $startDate      = $request->get('start_date');
            $endDate        = $request->get('end_date');
            $excludedOwners = $request->get('excluded_owners');

            $startDate      = is_string($startDate) ? $startDate : '';
            $endDate        = is_string($endDate) ? $endDate : '';
            $excludedOwners = is_array($excludedOwners) ? $excludedOwners : [];

            if (!empty($endDate) && empty($startDate)) {
                return $this->createJsonResponse(
                    'end date can\'t be defined without a start date',
                    [],
                    HttpStatusCodeEnum::HTTP_STATUS_CODE_BAD_REQUEST
                );
            }

            $filterBuilder = new RevenueFilterEntityBuilder($startDate, $endDate, $excludedOwners);
            $filterBuilder->build();

            $data = $this->extractionService->getRevenue($filterBuilder->getRevenueFilter());

            if ($data === null) {
                return $this->createJsonResponse('invalid request', [], HttpStatusCodeEnum::HTTP_STATUS_CODE_BAD_REQUEST);
            }

            return $this->createJsonResponse('Revenue detail obtained', $data);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf(
                    'API Active Campaigns error: %s start_date: %s end_date: %s trace: %s',
                    $e->getMessage(),
                    $request->get('start_date'),
                    $request->get('end_date'),
                    $e->getTraceAsString()
                )
            );

            return $this->createJsonResponse($e->getMessage(), [], HttpStatusCodeEnum::HTTP_STATUS_CODE_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get contact history
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function contactHistoryAction(Request $request): JsonResponse
    {
        try {
            $this->fosUserAuthenticationService->checkCredentials($request);
        } catch (\Exception $exception) {
            return $this->createJsonResponse($exception->getMessage(), [], $exception->getCode());
        }

        $email = $request->get(Contact::EMAIL_KEY, '');
        $phone = $request->get(Lead::PHONE_KEY, '');

        // Find all leads from email and phone
        $leads = $this->leadService->getLeads($email, $phone);

        if (count($leads) <= 0) {
            $this->logger->info(static::CONTACTS_NOT_FOUND_MESSAGE);
        }

        $responseData = [];

        foreach ($leads as $lead) {
            $extractionContacts = $this->extractionContactService->getContactHistoryByLead($lead);

            foreach ($extractionContacts as $extractionContact) {
                $extraction     = $extractionContact->getExtraction();
                $campaign       = $extraction->getCampaign();
                $contact        = $extractionContact->getContact();
                $owner          = $contact->getOwner();
                $responseData[] = [
                    'lead_id'    => $lead->getId(),
                    'contact_id' => $contact->getId(),
                    'client_id ' => $campaign->getClient()->getId(),
                    'campaign'   => [
                        'name'               => $campaign->getName(),
                        'notification_email' => $campaign->getNotificationEmailAddress(),
                    ],
                    'owner'      => [
                        'name'               => $owner->getName(),
                        'notification_email' => $owner->getNotificationEmailAddress(),
                    ],
                    'extraction' => $extraction->getName(),
                    'sold_at'    => $extraction->getSoldAt() instanceof DateTime
                        ? $extraction->getSoldAt()->format('Y-m-d')
                        : null,
                ];
            }
        }

        return $this->createJsonResponse('History detail obtained', $responseData);
    }

    /**
     * Request erasure contact
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function contactErasureAction(Request $request): JsonResponse
    {
        try {
            $this->fosUserAuthenticationService->checkCredentials($request);
        } catch (AccessDeniedException $exception) {
            return $this->createJsonResponse($exception->getMessage(), [], $exception->getCode());
        }

        $email = $request->request->get(Contact::EMAIL_KEY, '');
        $phone = $request->request->get(Lead::PHONE_KEY, '');

        // Find all leads from email and phone
        $leads = $this->leadService->getLeads($email, $phone);

        // Find all leads historic from email and phone
        $leadsHist = $this->leadService->getLeadsHist($email, $phone);

        if (count($leads) <= 0 && count($leadsHist) <= 0) {
            $this->logger->info(static::CONTACTS_NOT_FOUND_MESSAGE);
        }

        // Obfuscate all leads found
        $obfuscated = $this->contactObfuscationService->obfuscateAllContactData($leads, $email, $phone);

        // Obfuscate all leads found on historic
        $obfuscatedHist = $this->contactObfuscationService->obfuscateAllContactHistData($leadsHist, $email, $phone);

        if ($obfuscated === false || $obfuscatedHist === false) {
            return $this->createJsonResponse(static::COULD_NOT_ERASE_CONTACT_MESSAGE, [], HttpStatusCodeEnum::HTTP_STATUS_CODE_INTERNAL_SERVER_ERROR);
        }

        return $this->createJsonResponse(static::CONTACT_EMAIL_ERASURE_MESSAGE);
    }

    /**
     * Create campaign based on request data
     * Returns created campaign's id
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createCampaignAction(Request $request): JsonResponse
    {
        try {
            $this->fosUserAuthenticationService->checkCredentials($request);

            // get post data
            $clientId                 = $request->request->get(Campaign::CLIENT_ID);
            $name                     = $request->request->get(Campaign::NAME);
            $description              = $request->request->get(Campaign::DESCRIPTION);
            $externalCampaignId       = $request->request->get(Campaign::EXTERNAL_ID);
            $notificationEmailAddress = $request->request->get(Campaign::NOTIFICATION_EMAIL_ADDRESS);

            $this->checkRequiredFields([Campaign::CLIENT_ID => $clientId, Campaign::NAME => $name]);

            $campaignData = [
                Campaign::CLIENT_ID                  => $clientId,
                Campaign::NAME                       => $name,
                Campaign::DESCRIPTION                => $description,
                Campaign::EXTERNAL_ID                => $externalCampaignId,
                Campaign::NOTIFICATION_EMAIL_ADDRESS => $notificationEmailAddress,
            ];

            $newCampaign = $this->campaignService->createCampaign($campaignData);
        } catch (AccessDeniedException $exception) {
            return $this->createJsonResponse($exception->getMessage(), [], $exception->getCode());
        } catch (DBALException $exception){
            return $this->createJsonResponse(
                self::CAMPAIGN_NOT_CREATED_MESSAGE,
                [],
                HttpStatusCodeEnum::HTTP_STATUS_CODE_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $exception) {
            return $this->createJsonResponse(
                $exception->getMessage(),
                [],
                $exception->getCode()
            );
        }

        return $this->createJsonResponse(
            static::CAMPAIGN_CREATED_MESSAGE,
            ['id' => $newCampaign->getId()]
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createExtractionAction(Request $request): JsonResponse
    {
        try {
            $this->fosUserAuthenticationService->checkCredentials($request);

            $extractionFilter = ExtractionFilter::buildExtractionFilterFromRequest($request->request->all());

            $this->checkRequiredFields(
                [
                    Extraction::CAMPAIGN_ID         => $extractionFilter->getCampaignId(),
                    Extraction::NAME                => $extractionFilter->getName(),
                    Extraction::QUANTITY            => $extractionFilter->getQuantity(),
                    Extraction::PAYOUT              => $extractionFilter->getPayout(),
                    AudiencesFilter::FILTER_OWNER   => $extractionFilter->getOwner(),
                    AudiencesFilter::FILTER_COUNTRY => $extractionFilter->getCountry()
                ]
            );

            if (!$extractionFilter->isValid()) {
                $errorMessage = [];
                foreach ($extractionFilter->getInvalidations() as $invalidationField => $invalidationMessage) {
                    $errorMessage [$invalidationField] = $invalidationMessage;
                }

                return $this->createJsonResponse(
                    self::INVALID_FILTER_MESSAGE,
                    $errorMessage,
                    HttpStatusCodeEnum::HTTP_STATUS_CODE_BAD_REQUEST
                );
            }

            $newExtraction  = $this->extractionService->createExtraction($extractionFilter);
        } catch (AccessDeniedException $exception) {
            return $this->createJsonResponse($exception->getMessage(), [], $exception->getCode());
        } catch (DBALException $exception) {
            return $this->createJsonResponse(
                self::EXTRACTION_NOT_CREATED_MESSAGE,
                [],
                HttpStatusCodeEnum::HTTP_STATUS_CODE_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $exception) {
            return $this->createJsonResponse($exception->getMessage(), [], HttpStatusCodeEnum::HTTP_STATUS_CODE_BAD_REQUEST);
        }

        return $this->createJsonResponse(static::EXTRACTION_CREATED_MESSAGE, ['id' => $newExtraction->getId()]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAudiencesAction(Request $request): JsonResponse
    {
        try {
            $this->fosUserAuthenticationService->checkCredentials($request);

            $filter = AudiencesFilter::buildAudiencesFilterFromRequest(
                $request->get(AudiencesFilter::FILTER, null),
                $request->get(AudiencesFilter::FIELDS, null)
            );

            $this->checkRequiredFields(
                [AudiencesFilter::FILTER_OWNER => $filter->getOwner(), AudiencesFilter::FILTER_COUNTRY => $filter->getCountry()]
            );

            if (!$filter->isValid()) {
                $errorMessage = [];
                foreach ($filter->getInvalidations() as $invalidationField => $invalidationMessage) {
                    $errorMessage [$invalidationField] = $invalidationMessage;
                }

                return $this->createJsonResponse(
                    self::INVALID_FILTER_MESSAGE,
                    $errorMessage,
                    HttpStatusCodeEnum::HTTP_STATUS_CODE_BAD_REQUEST
                );
            }

            $audiences = $this->statisticsService->getAudiences($filter);

        } catch (AccessDeniedException $exception) {
            return $this->createJsonResponse($exception->getMessage(), [], $exception->getCode());
        } catch (\Exception $exception) {
            return $this->createJsonResponse(
                $exception->getMessage(),
                [],
                $exception->getCode()
            );
        }

        return $this->createJsonResponse(self::AUDIENCE_DETAIL_OBTAINED_MESSAGE, $audiences);
    }

    /**
     * Closes an extraction from a given extraction_id
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function closeExtractionAction(Request $request): JsonResponse
    {
        try {
            $this->fosUserAuthenticationService->checkCredentials($request);
        } catch (AccessDeniedException $exception) {
            return $this->createJsonResponse($exception->getMessage(), $exception->getCode());
        }

        $extractionId = $request->get(Extraction::EXTRACTION_ID);

        try {
            $this->checkRequiredFields([Extraction::EXTRACTION_ID => $extractionId]);

            $this->extractionService->finishExtraction($extractionId);

        } catch (DBALException $exception){
            return $this->createJsonResponse(
                static::EXTRACTION_NOT_CLOSED_MESSAGE,
                [],
                HttpStatusCodeEnum::HTTP_STATUS_CODE_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $exception) {
            return $this->createJsonResponse($exception->getMessage(), [], $exception->getCode());
        }

        return $this->createJsonResponse(sprintf(static::EXTRACTION_CLOSED_MESSAGE, $extractionId));
    }

    /**
     * Returns contacts from a given extraction
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getExtractionContactsAction(Request $request): JsonResponse
    {
        try {
            $this->fosUserAuthenticationService->checkCredentials($request);
        } catch (AccessDeniedException $exception) {
            return $this->createJsonResponse($exception->getMessage(), $exception->getCode());
        }

        // Getting request info
        $extractionId = $request->get(Extraction::EXTRACTION_ID);
        $fields       = $request->get('fields', []);
        $page         = $request->get('page', 0);

        // Prepare request info
        $fields       = is_array($fields) ? $fields : [];
        $limit        = static::EXTRACTION_CONTACTS_PER_PAGE;
        $offset       = $page > 0 ? ($page - 1) * $limit : 0;

        try {
            $this->checkRequiredFields([Extraction::EXTRACTION_ID => $extractionId]);

            $results = $this->extractionService->getExtractionContacts($extractionId, $fields, $limit, $offset);
        } catch (DBALException $exception){
            return $this->createJsonResponse(
                static::EXTRACTION_CONTACTS_NOK_MESSAGE,
                [],
                HttpStatusCodeEnum::HTTP_STATUS_CODE_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $exception) {
            return $this->createJsonResponse($exception->getMessage(), [], $exception->getCode());
        }

        return $this->createJsonResponse(
            sprintf(static::EXTRACTION_CONTACTS_OK_MESSAGE, $extractionId),
            $results
        );
    }

    /**
     * Generates a Json Response
     *
     * @param string     $message
     * @param array|null $response
     * @param int|null   $code
     *
     * @return JsonResponse
     */
    private function createJsonResponse(string $message, ?array $response = [], ?int $code = 200)
    {
        return new JsonResponse(
            [
                'code'     => $code,
                'message'  => $message,
                'response' => $response,
            ], $code
        );
    }

    /**
     * Check required fields and return true if all of them are present
     *
     * @param array $requiredFields
     *
     * @return bool
     * @throws \Exception
     */
    private function checkRequiredFields(array $requiredFields): bool
    {
        $missingFields = [];

        foreach ($requiredFields as $fieldName => $requiredField) {
            if ($requiredField === null) {
                $missingFields[] = $fieldName;
            }
        }

        if (!empty($missingFields)) {
            throw new \Exception(sprintf('Missing required fields: %s', implode(', ', $missingFields)), HttpStatusCodeEnum::HTTP_STATUS_CODE_BAD_REQUEST);
        }

        return true;
    }
}
