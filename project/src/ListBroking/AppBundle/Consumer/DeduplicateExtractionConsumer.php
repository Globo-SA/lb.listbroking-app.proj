<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Consumer;

use ListBroking\AppBundle\Entity\Extraction;
use Listbroking\AppBundle\Monolog\Processor\ServiceLogTaskIdentifierInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ExtractionServiceInterface;
use ListBroking\AppBundle\Service\Helper\FileHandlerServiceInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class DeduplicateExtractionConsumer implements ConsumerInterface
{
    const LOGGER_IDENTIFIER = 'RabbitMQ-DeduplicateExtractionConsumer';

    /**
     * @var ExtractionServiceInterface
     */
    private $e_service;

    /**
     * @var FileHandlerServiceInterface
     */
    private $f_service;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * DeduplicateExtractionConsumer constructor.
     *
     * @param ExtractionServiceInterface        $e_service
     * @param FileHandlerServiceInterface       $f_service
     * @param LoggerInterface                   $logger
     * @param ServiceLogTaskIdentifierInterface $serviceLogTaskIdentifier
     */
    public function __construct(
        ExtractionServiceInterface $e_service,
        FileHandlerServiceInterface $f_service,
        LoggerInterface $logger,
        ServiceLogTaskIdentifierInterface $serviceLogTaskIdentifier
    ) {
        $this->e_service = $e_service;
        $this->f_service = $f_service;
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
            $this->e_service->logExtractionAction(
                $extraction,
                sprintf('Starting "deduplicateExtraction" with field: %s', $msg_body['field'])
            );
            $this->logger->info(
                sprintf('Starting "deduplicateExtraction" with field: %s', $msg_body['field']),
                ['extraction_id' => $extraction->getId()]
            );

            $file = $this->f_service->loadExcelFile($msg_body['filename']);

            $this->logger->info(
                'Deduplication file loaded successfully',
                ['extraction_id' => $extraction->getId(), 'filename' => $msg_body['filename']]
            );

            // Persist deduplications to the DB
            $this->e_service->uploadDeduplicationsByFile($extraction, $file, $msg_body['field']);

            $this->logger->info(
                'Deduplication uploaded to database',
                ['extraction_id' => $extraction->getId(), 'field' => $msg_body['field']]
            );

            // Filter extraction
            $extraction->setDeduplicationType($msg_body['deduplication_type']);

            if (empty($msg_body['skip_extracting'])) {
                $this->logger->info('Starting extraction with deduplication', ['extraction_id' => $extraction->getId()]);

                $result = $this->e_service->runExtraction($extraction)
                    ? sprintf('EXTRACTED %d contacts', count($extraction->getExtractionContacts()))
                    : 'NOT EXTRACTED!';

                $this->logger->info(
                    sprintf('Ending extraction with deduplication, result: %s', $result),
                    ['extraction_id' => $extraction->getId()]
                );
            }

            $extraction->setIsDeduplicating(false);

            // Save changes
            $this->e_service->updateEntity($extraction);

            $this->e_service->logExtractionAction($extraction, 'Ending "deduplicateExtraction", result: DONE');
            $this->logger->info('Ending "deduplicateExtraction", result: DONE', ['extraction_id' => $extraction->getId()]);
        } catch (\Exception $exception) {
            if ($extraction instanceof Extraction) {
                $this->e_service->logExtractionAction($extraction, 'Error "deduplicateExtraction"');
                $extraction->setIsAlreadyExtracted(true);
                $extraction->setIsDeduplicating(false);
                $this->e_service->updateEntity($extraction);
            }

            $this->logger->error(
                '#LB-0016# Error on "deduplication"',
                [
                    'extraction_id' => $extraction instanceof Extraction ? $extraction->getId() : null,
                    'result'        => $exception->getMessage()
                ]
            );
        }

        return true;
    }
}
