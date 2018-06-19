<?php

namespace ListBroking\AppBundle\Controller;

use ListBroking\AppBundle\Entity\Client;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Exception\Validation\LeadValidationException;
use ListBroking\AppBundle\Service\Authentication\FosUserAuthenticationServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ContactObfuscationServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ExtractionServiceInterface;
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
    const HTTP_SERVER_ERROR_CODE                    = 500;
    const HTTP_BAD_REQUEST_CODE                     = 400;
    const HTTP_UNAUTHORIZED_CODE                    = 401;
    const CONTACT_EMAIL_ERASURE_MESSAGE             = 'Contact has been erased';
    const CODE_KEY                                  = 'code';
    const MESSAGE_KEY                               = 'message';
    const ERASE_CONTACT_ERROR_MESSAGE               = '#LB-0041# Unable to erase contact';
    const MISSING_EMAIL_OR_PHONE_PARAMETER_MESSAGE  = 'Missing email or phone parameter';

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
     * @var FosUserAuthenticationServiceInterface $fosUserAuthenticationService
     */
    private $fosUserAuthenticationService;

    /**
     * @var ContactObfuscationServiceInterface
     */
    private $contactObfuscationService;

    /**
     * APIController constructor.
     *
     * @param LoggerInterface $logger
     * @param StagingServiceInterface $stagingService
     * @param ExtractionServiceInterface $extractionService
     * @param FosUserAuthenticationServiceInterface $fosUserAuthenticationService
     * @param ContactObfuscationServiceInterface $contactObfuscationService
     */
    public function __construct(
        LoggerInterface $logger,
        StagingServiceInterface $stagingService,
        ExtractionServiceInterface $extractionService,
        FosUserAuthenticationServiceInterface $fosUserAuthenticationService,
        ContactObfuscationServiceInterface $contactObfuscationService
    ) {
        $this->logger                       = $logger;
        $this->stagingService               = $stagingService;
        $this->extractionService            = $extractionService;
        $this->fosUserAuthenticationService = $fosUserAuthenticationService;
        $this->contactObfuscationService    = $contactObfuscationService;
    }


    /**
     * Dumb Action that just saves the lead for the ETL process
     *
     * @param Request $request
     *
     * @throws LeadValidationException
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createStagingLeadAction (Request $request)
    {
        try
        {
            $this->fosUserAuthenticationService->checkCredentials($request);

            $lead = $request->get('lead');

            if ( ! $lead )
            {
                throw new LeadValidationException('Lead is empty');
            }

            $this->stagingService->addStagingContact($lead);
            $this->stagingService->flushAll();
            $this->stagingService->clearEntityManager();

            return $this->createJsonResponse('Lead added');
        }
        catch ( \Exception $e )
        {
            return $this->createJsonResponse($e->getMessage(), self::HTTP_SERVER_ERROR_CODE);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getActiveCampaignsAction(Request $request)
    {
        try
        {
            $this->fosUserAuthenticationService->checkCredentials($request);

            $end_date = $request->get('end_date');
            $start_date = $request->get('start_date');
            if ($start_date == null)
            {
                if ($end_date != null)
                {
                    return $this->createJsonResponse(['error' => 'end date can\'t be defined without a start date'], self::HTTP_BAD_REQUEST_CODE);
                }
                $start_date = date('Y-m-1');
            }
            if ($end_date == null)
            {
                $end_date = date('Y-m-t');
            }
            $data = $this->extractionService->getActiveCampaigns($start_date, $end_date, $request->get('page', 1), $request->get('page_size', self::HTTP_SERVER_ERROR_CODE));
            if ($data == null)
            {
                return $this->createJsonResponse(['error' => 'invalid request'], self::HTTP_BAD_REQUEST_CODE);
            }
            return $this->createJsonResponse($data);
        } catch (\Exception $e)
        {
            $this->logger->error(sprintf('API Active Campaigns error: %s start_date: %s end_date: %s trace: %s', $e->getMessage(), $request->get('start_date'), $request->get('end_date'), $e->getTraceAsString()));
            return $this->createJsonResponse($e->getMessage(), self::HTTP_SERVER_ERROR_CODE);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getExtractionsRevenueAction(Request $request)
    {
        try {
            $this->fosUserAuthenticationService->checkCredentials($request);

            $startDate = $request->get('start_date');
            $endDate   = $request->get('end_date');

            if ($startDate == null) {
                if ($endDate != null) {

                    return $this->createJsonResponse(
                        ['error' => 'end date can\'t be defined without a start date'],
                        self::HTTP_BAD_REQUEST_CODE
                    );
                }

                $startDate = date('Y-m-1');
            }

            if ($endDate == null) {
                $endDate = date('Y-m-t');
            }

            $data = $this->extractionService->getRevenue($startDate, $endDate);

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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function contactErasureAction(Request $request)
    {
        try {
            $this->fosUserAuthenticationService->checkCredentials($request);
        } catch (AccessDeniedException $exception) {
            return $this->createJsonResponse($exception->getMessage(), $exception->getCode());
        }

        $email          = $request->get(Contact::EMAIL_KEY, '');
        $phone          = $request->get(Lead::PHONE_KEY, '');
        $notifyClient   = $request->get(Client::NOTIFY_KEY, Client::NOTIFY_YES) == Client::NOTIFY_NO;

        if ($email == '' && $phone == ''){
            return $this->createJsonResponse(
                static::MISSING_EMAIL_OR_PHONE_PARAMETER_MESSAGE,
                static::HTTP_BAD_REQUEST_CODE
            );
        }

        // Obfuscate email
        try {
            $this->contactObfuscationService->obfuscateContactByEmail($email, $notifyClient);
        } catch (\Exception $exception) {
            $this->logger->error(
                static::ERASE_CONTACT_ERROR_MESSAGE,
                [
                    static::CODE_KEY    => $exception->getCode(),
                    static::MESSAGE_KEY => $exception->getMessage(),
                ]
            );
        }

        // Obfuscate phone
        try {
            $this->contactObfuscationService->obfuscateContactByPhone($phone, $notifyClient);
        } catch (\Exception $exception) {
            $this->logger->error(
                static::ERASE_CONTACT_ERROR_MESSAGE,
                [
                    static::CODE_KEY    => $exception->getCode(),
                    static::MESSAGE_KEY => $exception->getMessage(),
                ]
            );
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
    private function createJsonResponse ($response, $code = 200)
    {
        return new JsonResponse([
            'code'     => $code,
            'response' => $response
        ], $code);
    }
}
