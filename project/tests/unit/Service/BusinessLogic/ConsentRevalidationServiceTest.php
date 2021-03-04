<?php

namespace ListBroking\Tests\Unit\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Configuration;
use ListBroking\AppBundle\Entity\ConsentRevalidation;
use ListBroking\AppBundle\Repository\ConfigurationRepositoryInterface;
use ListBroking\AppBundle\Repository\ConsentRevalidationRepositoryInterface;
use ListBroking\AppBundle\Repository\ContactRepositoryInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ConsentRevalidationService;
use ListBroking\AppBundle\Service\External\IntegromatServiceInterface;
use ListBroking\AppBundle\Service\External\PhoneNumberServiceInterface;
use ListBroking\AppBundle\Service\External\TwilioServiceInterface;
use ListBroking\AppBundle\Service\Factory\ConsentRevalidationFactoryInterface;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ConsentRevalidationServiceTest extends TestCase
{
    /**
     * @var ContactRepositoryInterface
     */
    private $contactRepositoryMock;

    /**
     * @var ConsentRevalidationRepositoryInterface
     */
    private $consentRevalidationRepositoryMock;

    /**
     * @var ConfigurationRepositoryInterface
     */
    private $configurationRepositoryMock;

    /**
     * @var ConsentRevalidationFactoryInterface
     */
    private $consentRevalidationFactoryMock;

    /**
     * @var TwilioServiceInterface
     */
    private $twilioServiceMock;

    /**
     * @var PhoneNumberServiceInterface
     */
    private $phoneNumberServiceMock;

    /**
     * @var IntegromatServiceInterface
     */
    private $integromatServiceMock;

    /**
     * @var UrlGeneratorInterface
     */
    private $routerMock;

    /**
     * @var Logger
     */
    private $loggerMock;

    /**
     * @var string
     */
    private $twilioRevalidationFlowIdMock = '123';

    /**
     * @var string
     */
    private $twilioRevalidationPhoneNumberMock = '910000000';

    /**
     * @var string
     */
    private $twilioDatabaseUsernameMock = 'twilio';

    /**
     * @var string
     */
    private $twilioDatabaseTokenMock = '123456';

    /**
     * @var ConsentRevalidationService
     */
    private $consentRevalidationService;

    /**
     * {@inheritDoc}
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->contactRepositoryMock             = $this->createMock(ContactRepositoryInterface::class);
        $this->consentRevalidationRepositoryMock = $this->createMock(ConsentRevalidationRepositoryInterface::class);
        $this->configurationRepositoryMock       = $this->createMock(ConfigurationRepositoryInterface::class);
        $this->consentRevalidationFactoryMock    = $this->createMock(ConsentRevalidationFactoryInterface::class);
        $this->twilioServiceMock                 = $this->createMock(TwilioServiceInterface::class);
        $this->phoneNumberServiceMock            = $this->createMock(PhoneNumberServiceInterface::class);
        $this->integromatServiceMock             = $this->createMock(IntegromatServiceInterface::class);
        $this->routerMock                        = $this->createMock(UrlGeneratorInterface::class);
        $this->loggerMock                        = $this->createMock(Logger::class);

        $this->consentRevalidationService = new ConsentRevalidationService(
            $this->contactRepositoryMock,
            $this->consentRevalidationRepositoryMock,
            $this->configurationRepositoryMock,
            $this->consentRevalidationFactoryMock,
            $this->twilioServiceMock,
            $this->phoneNumberServiceMock,
            $this->integromatServiceMock,
            $this->routerMock,
            $this->loggerMock,
            $this->twilioRevalidationFlowIdMock,
            $this->twilioRevalidationPhoneNumberMock,
            $this->twilioDatabaseUsernameMock,
            $this->twilioDatabaseTokenMock
        );
    }

    public function testShouldThrowExceptionWhenEndCallAudioIsMissing(): void
    {
        $configurationMock = $this->createConfiguredMock(Configuration::class, ['getValue' => '']);

        $this->configurationRepositoryMock
            ->method('findOneByName')
            ->willReturn($configurationMock);

        $this->expectException('Exception');

        $this->consentRevalidationService->revalidateWithIVR(2021, 'PT', 'ADCLICK', 1);
    }

    public function testShouldThrowExceptionWhenAcceptingAnUnknownConsent(): void
    {
        $this->consentRevalidationRepositoryMock
            ->method('getById')
            ->willReturn(null);

        $this->expectException('Exception');

        $this->consentRevalidationService->acceptConsent(123);
    }

    public function testShouldThrowExceptionWhenAcceptingARejectedConsent(): void
    {
        $consent = $this->createConfiguredMock(ConsentRevalidation::class, [
            'getStatus' => ConsentRevalidation::STATUS_REJECTED
        ]);

        $this->consentRevalidationRepositoryMock
            ->method('getById')
            ->willReturn($consent);

        $this->expectException('Exception');

        $this->consentRevalidationService->acceptConsent(123);
    }

    public function testShouldThrowExceptionWhenAcceptingAnAcceptedConsent(): void
    {
        $consent = $this->createConfiguredMock(ConsentRevalidation::class, [
            'getStatus' => ConsentRevalidation::STATUS_ACCEPTED
        ]);

        $this->consentRevalidationRepositoryMock
            ->method('getById')
            ->willReturn($consent);

        $this->expectException('Exception');

        $this->consentRevalidationService->acceptConsent(123);
    }

    public function testShouldThrowExceptionWhenRejectingAnUnknownConsent(): void
    {
        $this->consentRevalidationRepositoryMock
            ->method('getById')
            ->willReturn(null);

        $this->expectException('Exception');

        $this->consentRevalidationService->rejectConsent(123);
    }

    public function testShouldThrowExceptionWhenRejectingARejectedConsent(): void
    {
        $consent = $this->createConfiguredMock(ConsentRevalidation::class, [
            'getStatus' => ConsentRevalidation::STATUS_REJECTED
        ]);

        $this->consentRevalidationRepositoryMock
            ->method('getById')
            ->willReturn($consent);

        $this->expectException('Exception');

        $this->consentRevalidationService->rejectConsent(123);
    }

    public function testShouldThrowExceptionWhenRejectingAnAcceptedConsent(): void
    {
        $consent = $this->createConfiguredMock(ConsentRevalidation::class, [
            'getStatus' => ConsentRevalidation::STATUS_ACCEPTED
        ]);

        $this->consentRevalidationRepositoryMock
            ->method('getById')
            ->willReturn($consent);

        $this->expectException('Exception');

        $this->consentRevalidationService->rejectConsent(123);
    }
}
