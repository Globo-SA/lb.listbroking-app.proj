<?php

namespace ListBroking\AppBundle\Controller;

use DateTime;
use ListBroking\AppBundle\Builder\Entity\RevenueFilterEntityBuilder;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Exception\Validation\LeadValidationException;
use ListBroking\AppBundle\Service\Authentication\FosUserAuthenticationServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ClientNotificationServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ContactObfuscationServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ExtractionContactServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ExtractionServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\LeadServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\StagingServiceInterface;
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
    const HTTP_SERVER_ERROR_CODE                   = 500;
    const HTTP_BAD_REQUEST_CODE                    = 400;
    const HTTP_NOT_FOUND_CODE                      = 404;
    const CONTACT_EMAIL_ERASURE_MESSAGE            = 'Contact has been erased';
    const MISSING_EMAIL_OR_PHONE_PARAMETER_MESSAGE = 'Missing email or phone parameter';
    const CONTACTS_NOT_FOUND_MESSAGE               = 'Contacts not found. Nothing to do';
    const COULD_NOT_ERASE_CONTACT_MESSAGE          = 'An error occurred while erasing contact.';

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
     */
    public function __construct(
        LoggerInterface $logger,
        StagingServiceInterface $stagingService,
        ExtractionServiceInterface $extractionService,
        ExtractionContactServiceInterface $extractionContactService,
        FosUserAuthenticationServiceInterface $fosUserAuthenticationService,
        ContactObfuscationServiceInterface $contactObfuscationService,
        LeadServiceInterface $leadService,
        ClientNotificationServiceInterface $clientNotificationService
    ) {
        $this->logger                       = $logger;
        $this->stagingService               = $stagingService;
        $this->extractionService            = $extractionService;
        $this->extractionContactService     = $extractionContactService;
        $this->fosUserAuthenticationService = $fosUserAuthenticationService;
        $this->contactObfuscationService    = $contactObfuscationService;
        $this->leadService                  = $leadService;
        $this->clientNotificationService    = $clientNotificationService;
    }


    /**
     * Dumb Action that just saves the lead for the ETL process
     *
     * @param Request $request
     *
     * @throws LeadValidationException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
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
            return $this->createJsonResponse($e->getMessage(), self::HTTP_SERVER_ERROR_CODE);
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
                        ['error' => 'end date can\'t be defined without a start date'],
                        self::HTTP_BAD_REQUEST_CODE
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
                    self::HTTP_SERVER_ERROR_CODE
                )
            );
            if ($data == null) {
                return $this->createJsonResponse(['error' => 'invalid request'], self::HTTP_BAD_REQUEST_CODE);
            }

            return $this->createJsonResponse($data);
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

            return $this->createJsonResponse($e->getMessage(), self::HTTP_SERVER_ERROR_CODE);
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
                    ['error' => 'end date can\'t be defined without a start date'],
                    self::HTTP_BAD_REQUEST_CODE
                );
            }

            $filterBuilder = new RevenueFilterEntityBuilder($startDate, $endDate, $excludedOwners);
            $filterBuilder->build();

            $data = $this->extractionService->getRevenue($filterBuilder->getRevenueFilter());

            if ($data === null) {
                return $this->createJsonResponse(['error' => 'invalid request'], self::HTTP_BAD_REQUEST_CODE);
            }

            return $this->createJsonResponse($data);
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

            return $this->createJsonResponse($e->getMessage(), self::HTTP_SERVER_ERROR_CODE);
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
            return $this->createJsonResponse($exception->getMessage(), $exception->getCode());
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

        return $this->createJsonResponse($responseData);
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
            return $this->createJsonResponse($exception->getMessage(), $exception->getCode());
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
            return $this->createJsonResponse(static::COULD_NOT_ERASE_CONTACT_MESSAGE, static::HTTP_SERVER_ERROR_CODE);
        }

        return $this->createJsonResponse(static::CONTACT_EMAIL_ERASURE_MESSAGE);
    }

    /**
     * Generates a Json Response
     *
     * @param     $response
     * @param int $code
     *
     * @return JsonResponse
     */
    private function createJsonResponse($response, $code = 200)
    {
        return new JsonResponse(
            [
                'code'     => $code,
                'response' => $response,
            ], $code
        );
    }
}
