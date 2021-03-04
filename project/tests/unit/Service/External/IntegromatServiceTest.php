<?php

namespace ListBroking\Tests\Unit\Service\External;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Country;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Service\External\IntegromatService;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class IntegromatServiceTest extends TestCase
{
    /**
     * @var Client
     */
    private $guzzleClientMock;

    /**
     * @var Logger
     */
    private $loggerMock;

    /**
     * @var string
     */
    private $requestOppositionWebhookMock = '';

    /**
     * @var IntegromatService
     */
    private $integromatService;

    /**
     * IntegromatServiceTest constructor.
     *
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->guzzleClientMock = $this->createMock(Client::class);
        $this->loggerMock       = $this->createMock(Logger::class);

        $this->integromatService = new IntegromatService(
            $this->guzzleClientMock,
            $this->loggerMock,
            $this->requestOppositionWebhookMock
        );
    }

    public function testRequestOppositionReturnTrueWithCodeStatus200()
    {
        $contactMock = $this->createConfiguredMock(Contact::class, [
            'getCountry' => $this->createConfiguredMock(Country::class, ['getName' => 'PT']),
            'getLead'    => $this->createConfiguredMock(Lead::class, ['getPhone' => '910000000']),
            'getEmail'   => 'test@test.com'
        ]);

        $requestMock = $this->createConfiguredMock(RequestInterface::class, [
            'send' => $this->createConfiguredMock(Response::class, [
                'getStatusCode' => 200
            ])
        ]);

        $this->guzzleClientMock
            ->method('post')
            ->willReturn($requestMock);

        $requested = $this->integromatService->requestOpposition($contactMock);

        $this->assertTrue($requested);
    }
}
