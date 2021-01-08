<?php

namespace ListBroking\AppBundle\Service\External;

use Twilio\Rest\Studio\V2\Flow\Execution\ExecutionContextInstance;
use Twilio\Rest\Studio\V2\Flow\ExecutionInstance;

interface TwilioServiceInterface
{
    /**
     * Triggers a Twilio studio flow given its ID, "from" and "to" phone numbers
     *
     * @param string $flowId
     * @param string $from
     * @param string $to
     * @param array  $parameters
     *
     * @return ExecutionInstance
     */
    public function createStudioExecution(
        string $flowId,
        string $from,
        string $to,
        array $parameters
    ): ExecutionInstance;

    /**
     * Returns an instance of a twilio studio execution
     *
     * @param string $flowId
     * @param string $executionId
     *
     * @return ExecutionContextInstance
     */
    public function getStudioExecutionData(string $flowId, string $executionId): ExecutionContextInstance;
}