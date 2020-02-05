<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Consumer;

use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Monolog\Processor\ServiceLogTaskIdentifierInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ExtractionServiceInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class LockExtractionConsumer implements ConsumerInterface
{
    const LOGGER_IDENTIFIER = 'RabbitMQ-LockExtractionConsumer';

    /**
     * @var ExtractionServiceInterface
     */
    private $e_service;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * LockExtractionConsumer constructor.
     *
     * @param ExtractionServiceInterface        $e_service
     * @param LoggerInterface                   $logger
     * @param ServiceLogTaskIdentifierInterface $serviceLogTaskIdentifier
     */
    public function __construct (
        ExtractionServiceInterface $e_service,
        LoggerInterface $logger,
        ServiceLogTaskIdentifierInterface $serviceLogTaskIdentifier
    ) {
        $this->e_service = $e_service;
        $this->logger    = $logger;

        $serviceLogTaskIdentifier->setLogIdentifier(self::LOGGER_IDENTIFIER);
    }

    /**
     * @param AMQPMessage $msg The message
     *
     * @return mixed false to reject and requeue, any other value to acknowledge
     */
    public function execute (AMQPMessage $msg)
    {
        $extraction = null;

        try {
            // PHP is run in shared nothing architecture, so long running processes need to
            // Clear the entity manager before running
            $this->e_service->clearEntityManager();

            $msg_body = unserialize($msg->body);

            $extraction = $this->e_service->findExtraction($msg_body['object_id']);
            $this->e_service->logExtractionAction($extraction, 'Starting "lockExtraction"');
            $this->logger->info(sprintf('Starting "lockExtraction"'), ['extraction_id' => $extraction->getId()]);

            // Generate locks
            $this->e_service->generateLocks($extraction, $msg_body['lock_types']);

            // Generate contact campaign history
            $this->e_service->generateContactCampaignHistory($extraction);

            // Close extraction
            $extraction->setIsLocking(false);

            // Save changes
            $this->e_service->updateEntity($extraction);

            $this->e_service->logExtractionAction($extraction, 'Ending "lockExtraction", result: Locks created');
            $this->logger->info(
                sprintf('Ending "lockExtraction" with success'), ['extraction_id' => $extraction->getId()]
            );
        }
        catch (\Exception $exception) {
            if ($extraction instanceof Extraction) {
                $this->e_service->logExtractionAction($extraction, sprintf('Error "lockExtraction"'));

                // reset locking status
                $extraction->setIsLocking(false);
                $extraction->setStatus(Extraction::STATUS_CONFIRMATION);
                $extraction->setSoldAt(null);
                $this->e_service->updateEntity($extraction);
            }

            $this->logger->error(
                '#LB-0017# Error on "lockExtraction"',
                [
                    'extraction_id' => $extraction instanceof Extraction ? $extraction->getId() : null,
                    'result'        => $exception->getMessage()
                ]
            );
        }

        return true;
    }
}
