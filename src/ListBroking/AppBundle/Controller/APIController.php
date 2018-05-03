<?php

namespace ListBroking\AppBundle\Controller;

use ListBroking\AppBundle\Exception\Validation\LeadValidationException;
use ListBroking\AppBundle\Service\Authentication\FosUserAuthenticationServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ExtractionServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\StagingServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class APIController
 */
class APIController extends Controller
{

    const HTTP_SERVER_ERROR_CODE = 500;
    const HTTP_BAD_REQUEST_CODE = 400;
    const HTTP_UNAUTHORIZED_CODE = 401;

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
     * APIController constructor.
     *
     * @param LoggerInterface                       $logger
     * @param StagingServiceInterface               $stagingService
     * @param ExtractionServiceInterface            $extractionService
     * @param FosUserAuthenticationServiceInterface $fosUserAuthenticationService
     */
    public function __construct(
        LoggerInterface $logger,
        StagingServiceInterface $stagingService,
        ExtractionServiceInterface $extractionService,
        FosUserAuthenticationServiceInterface $fosUserAuthenticationService
    ) {
        $this->logger                       = $logger;
        $this->stagingService               = $stagingService;
        $this->extractionService            = $extractionService;
        $this->fosUserAuthenticationService = $fosUserAuthenticationService;
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
