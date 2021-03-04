<?php

namespace ListBroking\AppBundle\Service\External;

use ListBroking\AppBundle\Service\Factory\TwilioClientFactoryInterface;
use Monolog\Logger;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Twilio\Rest\Studio\V2\Flow\Execution\ExecutionContextInstance;
use Twilio\Rest\Studio\V2\Flow\ExecutionInstance;

class TwilioService implements TwilioServiceInterface
{
    /**
     * @var Client
     */
    private $twilioRestClient;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * TwilioService constructor.
     *
     * @param TwilioClientFactoryInterface $twilioClientFactory
     * @param Logger                       $logger
     */
    public function __construct(TwilioClientFactoryInterface $twilioClientFactory, Logger $logger)
    {
        $this->twilioRestClient = $twilioClientFactory->createRestClient();
        $this->logger           = $logger;
    }

    /**
     * {@inheritDoc}
     *
     * @throws TwilioException
     */
    public function createStudioExecution(
        string $flowId,
        string $from,
        string $to,
        array $parameters
    ): ExecutionInstance {
        $this->logger->info('Triggering twilio flow', [
            'flow_id'    => $flowId,
            'from'       => $from,
            'to'         => $to,
            'parameters' => $parameters
        ]);

        $options = ['parameters' => $parameters];

        return $this->twilioRestClient
            ->studio
            ->v2
            ->flows($flowId)
            ->executions
            ->create($to, $from, $options);
    }

    /**
     * {@inheritDoc}
     *
     * @throws TwilioException
     */
    public function getStudioExecutionData(string $flowId, string $executionId): ExecutionContextInstance
    {
        $this->logger->info('Getting twilio studio execution instance', [
            'flow_id'      => $flowId,
            'execution_id' => $executionId
        ]);

        return $this->twilioRestClient
            ->studio
            ->v2
            ->flows($flowId)
            ->executions($executionId)
            ->executionContext()
            ->fetch();
    }
}