<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Consumer;

use ListBroking\AppBundle\Service\BusinessLogic\ExtractionServiceInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class RunExtractionConsumer implements ConsumerInterface
{

    /**
     * @var ExtractionServiceInterface
     */
    private $e_service;

    function __construct (ExtractionServiceInterface $e_service)
    {
        $this->e_service = $e_service;
    }

    /**
     * @param AMQPMessage $msg The message
     *
     * @return mixed false to reject and requeue, any other value to acknowledge
     */
    public function execute (AMQPMessage $msg)
    {
        try
        {
//            return true;
            // PHP is run in shared nothing architecture, so long running processes need to
            // Clear the entity manager before running
            $this->e_service->clearEntityManager();

            $msg_body = unserialize($msg->body);

            $extraction = $this->e_service->findExtraction($msg_body['object_id']);
            $this->e_service->logExtractionAction($extraction, 'Starting \'runExtraction\'');

            // Run Extraction
            $result = $this->e_service->runExtraction($extraction) ? 'EXTRACTED' : 'NOT EXTRACTED!';

            $this->e_service->logExtractionAction($extraction, sprintf('Ending \'runExtraction\', result: %s', $result));

            return true;
        }
        catch ( \Exception $e )
        {
            $this->e_service->logError($e);

            return false;
        }
    }
}