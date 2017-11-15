<?php
/**
 * Created by PhpStorm.
 * User: rbarros
 * Date: 11/10/17
 * Time: 5:38 PM
 */

namespace ListBroking\AppBundle\Controller;


use ListBroking\AppBundle\Exception\Validation\OppositionListException;
use ListBroking\AppBundle\Service\Authentication\FosUserAuthenticationServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\StagingService;
use ListBroking\AppBundle\Validation\RequestValidatorInterface;
use ListBroking\AppBundle\Validation\UnsubscribeFromLeadcentreRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class WebhookController
 */
class WebhookController
{
    private const LEADCENTRE_OPPOSITION_TYPE = 'LEADCENTRE';
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var StagingService $stagingService
     */
    private $stagingService;

    /**
     * @var RequestValidatorInterface $webhookrequestHandlerService
     */
    private $webhookrequestHandlerService;

    /**
     * @var FosUserAuthenticationServiceInterface $fosUserAuthenticationService
     */
    private $fosUserAuthenticationService;


    /**
     * WebhookController constructor.
     *
     * @param LoggerInterface                       $logger
     * @param StagingService                        $stagingService
     * @param RequestValidatorInterface             $requestValidator
     * @param FosUserAuthenticationServiceInterface $fosUserAuthenticationService
     */
    public function __construct(
        LoggerInterface $logger,
        StagingService $stagingService,
        RequestValidatorInterface $requestValidator,
        FosUserAuthenticationServiceInterface $fosUserAuthenticationService
    ) {
        $this->logger                       = $logger;
        $this->stagingService               = $stagingService;
        $this->webhookrequestHandlerService = $requestValidator;
        $this->fosUserAuthenticationService = $fosUserAuthenticationService;
    }


    /**
     * Adds a phone number to the opposition list so it will never be extracted
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function unsubscribeFromLeadcentreAction(Request $request): JsonResponse
    {

        try {
            $this->fosUserAuthenticationService->checkCredentials($request);
        } catch (AccessDeniedException $exception) {
            return new JsonResponse(['message' => 'Unauthorized', 'code' => 401], 401);
        }

        /** @var UnsubscribeFromLeadcentreRequest $unsubscribeFromLeadcentre */
        $unsubscribeFromLeadcentre = $this->webhookrequestHandlerService->validate($request);

        if (!$unsubscribeFromLeadcentre->isValid()) {
            return new JsonResponse(
                ['message' => 'Unauthorized', 'code' => 400, 'errors' => $unsubscribeFromLeadcentre->getErrors()], 400
            );
        };

        try {
            $this->stagingService->addPhoneToOppositionList(
                self::LEADCENTRE_OPPOSITION_TYPE,
                $unsubscribeFromLeadcentre->getPhone()
            );
        } catch (OppositionListException  $exception) {
            $this->logger->info($exception->getMessage());

            return new JsonResponse(['message' => 'Contact already exists', 'code' => 400], 400);
        }

        return new JsonResponse(
            [
                'message' =>
                    sprintf(
                        'Contact %s added to the opposition list %s',
                        $unsubscribeFromLeadcentre->getPhone(),
                        self::LEADCENTRE_OPPOSITION_TYPE
                    ),
                'code'    => 200,
            ]
        );
    }
}
