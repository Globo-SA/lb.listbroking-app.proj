<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use DateTime;
use Exception;
use ListBroking\AppBundle\Entity\Configuration;
use ListBroking\AppBundle\Entity\ConsentRevalidation;
use ListBroking\AppBundle\Repository\ConfigurationRepositoryInterface;
use ListBroking\AppBundle\Repository\ConsentRevalidationRepositoryInterface;
use ListBroking\AppBundle\Repository\ContactRepositoryInterface;
use ListBroking\AppBundle\Service\External\IntegromatServiceInterface;
use ListBroking\AppBundle\Service\External\PhoneNumberServiceInterface;
use ListBroking\AppBundle\Service\External\TwilioServiceInterface;
use ListBroking\AppBundle\Service\Factory\ConsentRevalidationFactoryInterface;
use Monolog\Logger;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ConsentRevalidationService implements ConsentRevalidationServiceInterface
{
    /**
     * @var ContactRepositoryInterface
     */
    private $contactRepository;

    /**
     * @var ConsentRevalidationRepositoryInterface
     */
    private $consentRevalidationRepository;

    /**
     * @var ConfigurationRepositoryInterface
     */
    private $configurationRepository;

    /**
     * @var ConsentRevalidationFactoryInterface
     */
    private $consentRevalidationFactory;

    /**
     * @var TwilioServiceInterface
     */
    private $twilioService;

    /**
     * @var PhoneNumberServiceInterface
     */
    private $phoneNumberService;

    /**
     * @var IntegromatServiceInterface
     */
    private $integromatService;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $twilioRevalidationFlowId;

    /**
     * @var string
     */
    private $twilioRevalidationPhoneNumber;

    /**
     * @var string
     */
    private $twilioDatabaseUsername;

    /**
     * @var string
     */
    private $twilioDatabaseToken;

    /**
     * ConsentRevalidationService constructor.
     *
     * @param ContactRepositoryInterface             $contactRepository
     * @param ConsentRevalidationRepositoryInterface $consentRevalidationRepository
     * @param ConfigurationRepositoryInterface       $configurationRepository
     * @param ConsentRevalidationFactoryInterface    $consentRevalidationFactory
     * @param TwilioServiceInterface                 $twilioService
     * @param PhoneNumberServiceInterface            $phoneNumberService
     * @param IntegromatServiceInterface             $integromatService
     * @param UrlGeneratorInterface                  $router
     * @param Logger                                 $logger
     * @param string                                 $domain
     * @param string                                 $twilioRevalidationFlowId
     * @param string                                 $twilioRevalidationPhoneNumber
     * @param string                                 $twilioDatabaseUsername
     * @param string                                 $twilioDatabaseToken
     */
    public function __construct(
        ContactRepositoryInterface $contactRepository,
        ConsentRevalidationRepositoryInterface $consentRevalidationRepository,
        ConfigurationRepositoryInterface $configurationRepository,
        ConsentRevalidationFactoryInterface $consentRevalidationFactory,
        TwilioServiceInterface $twilioService,
        PhoneNumberServiceInterface $phoneNumberService,
        IntegromatServiceInterface $integromatService,
        UrlGeneratorInterface $router,
        Logger $logger,
        string $domain,
        string $twilioRevalidationFlowId,
        string $twilioRevalidationPhoneNumber,
        string $twilioDatabaseUsername,
        string $twilioDatabaseToken
    ) {
        $this->contactRepository             = $contactRepository;
        $this->consentRevalidationRepository = $consentRevalidationRepository;
        $this->configurationRepository       = $configurationRepository;
        $this->consentRevalidationFactory    = $consentRevalidationFactory;
        $this->twilioService                 = $twilioService;
        $this->phoneNumberService            = $phoneNumberService;
        $this->integromatService             = $integromatService;
        $this->router                        = $router;
        $this->logger                        = $logger;
        $this->twilioRevalidationFlowId      = $twilioRevalidationFlowId;
        $this->twilioRevalidationPhoneNumber = $twilioRevalidationPhoneNumber;
        $this->twilioDatabaseUsername        = $twilioDatabaseUsername;
        $this->twilioDatabaseToken           = $twilioDatabaseToken;

        $this->setRouterContext($domain);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function revalidateWithIVR(
        int $year,
        string $countryCode,
        string $owner,
        int $limit,
        int $contactId = null
    ): array {
        // Get contacts
        $contacts = $this->contactRepository->getRandomContactsWithoutConsentRevalidations(
            $year,
            $countryCode,
            $owner,
            $limit,
            $contactId
        );

        // Get end call audio
        $configuration = $this->configurationRepository->findOneByName(Configuration::KEY_IVR_END_CALL_AUDIO_URL);

        if (!$configuration instanceof Configuration || empty($configuration->getValue())) {
            throw new Exception(sprintf('Configuration "%s" missing', Configuration::KEY_IVR_END_CALL_AUDIO_URL));
        }

        $endCallAudioUrl = $configuration->getValue();

        foreach ($contacts as $contact) {
            // Creates and saves a new revalidation database record
            $ivrRevalidation     = $this->consentRevalidationFactory->createIVRRevalidation($contact);
            $consentRevalidation = $this->consentRevalidationRepository->saveConsentRevalidation($ivrRevalidation);

            // Generate contact's phone with country code
            $phoneWithCountryCode = $this->phoneNumberService->getPhoneWithCountryCode(
                $contact->getLead()->getPhone(),
                $contact->getCountry()->getName()
            );

            // Get call user audio
            $callUserAudioUrl = $contact->getSource()->getBrand()->getIvrAudioUrl();

            // Generate response webhooks for accepting and rejecting consent
            $parameters           = [
                'id'       => $consentRevalidation->getId(),
                'username' => $this->twilioDatabaseUsername,
                'token'    => $this->twilioDatabaseToken
            ];
            $acceptConsentWebhook = $this->router->generate('api_accept_consent_revalidation', $parameters);
            $rejectConsentWebhook = $this->router->generate('api_reject_consent_revalidation', $parameters);

            // Trigger IVR call
            $execution = $this->twilioService->createStudioExecution(
                $this->twilioRevalidationFlowId,
                $this->twilioRevalidationPhoneNumber,
                $phoneWithCountryCode,
                [
                    'call_user_audio_url'    => $callUserAudioUrl,
                    'end_call_audio_url'     => $endCallAudioUrl,
                    'accept_consent_webhook' => $acceptConsentWebhook,
                    'reject_consent_webhook' => $rejectConsentWebhook
                ]
            );

            // Update database record with Twilio Execution Instance data
            $executionData = $this->twilioService->getStudioExecutionData($execution->flowSid, $execution->sid);
            $consentRevalidation->setDataAsArray($executionData->toArray());
            $this->consentRevalidationRepository->saveConsentRevalidation($consentRevalidation);

            $this->logger->info('Consent Revalidation sent to contact', ['contact_id' => $contact->getId()]);
        }

        return $contacts;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function acceptConsent(int $id): void
    {
        $consentRevalidation = $this->updateConsentById($id, ConsentRevalidation::STATUS_ACCEPTED);

        // Update contact
        $contact = $consentRevalidation->getContact();
        $contact->setDate(new DateTime());
        $this->contactRepository->saveContact($contact);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function rejectConsent(int $id): void
    {
        $consentRevalidation = $this->updateConsentById($id, ConsentRevalidation::STATUS_REJECTED);

        // Request opposition for the contact
        $this->integromatService->requestOpposition($consentRevalidation->getContact());
    }

    /**
     * Validates if a given consent_revalidation object is ready to be updated, and updates it
     *
     * @param int    $id
     * @param string $status
     *
     * @return ConsentRevalidation
     * @throws Exception
     */
    private function updateConsentById(int $id, string $status): ConsentRevalidation
    {
        // Find the database record to update
        $consentRevalidation = $this->consentRevalidationRepository->getById($id);

        if (!$consentRevalidation instanceof ConsentRevalidation) {
            throw new Exception('Consent Revalidation not found');
        }

        if ($consentRevalidation->getStatus() !== ConsentRevalidation::STATUS_NEW) {
            throw new Exception('This consent was already revalidated');
        }

        // Find Twilio updated data
        $executionData = $this->twilioService->getStudioExecutionData(
            $consentRevalidation->getDataByKey('flowSid'),
            $consentRevalidation->getDataByKey('executionSid')
        );

        // Update consent with new data
        $consentRevalidation->setStatus($status);
        $consentRevalidation->setDataAsArray($executionData->toArray());
        $this->consentRevalidationRepository->saveConsentRevalidation($consentRevalidation);

        return $consentRevalidation;
    }

    /**
     * @param string $domain
     */
    private function setRouterContext(string $domain): void
    {
        $domainParsed = parse_url($domain);

        $context = $this->router->getContext();
        $context->setHost($domainParsed['host']);
        $context->setScheme($domainParsed['scheme']);
    }
}