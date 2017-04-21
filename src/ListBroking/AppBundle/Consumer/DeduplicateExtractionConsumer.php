<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Consumer;

use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Service\BusinessLogic\ExtractionServiceInterface;
use ListBroking\AppBundle\Service\Helper\FileHandlerServiceInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class DeduplicateExtractionConsumer implements ConsumerInterface
{

    /**
     * @var ExtractionServiceInterface
     */
    private $e_service;

    /**
     * @var FileHandlerServiceInterface
     */
    private $f_service;

    public function __construct (ExtractionServiceInterface $e_service, FileHandlerServiceInterface $f_service)
    {
        $this->e_service = $e_service;
        $this->f_service = $f_service;
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
            // PHP is run in shared nothing architecture, so long running processes need to
            // Clear the entity manager before running
            $this->e_service->clearEntityManager();

            $msg_body = unserialize($msg->body);

            $extraction = $this->e_service->findExtraction($msg_body['object_id']);
            $this->e_service->logExtractionAction($extraction, sprintf('Starting \'deduplication\' with field: %s', $msg_body['field']));


            $file = $this->f_service->loadExcelFile($msg_body['filename']);

            // Persist deduplications to the DB
            $this->e_service->uploadDeduplicationsByFile($extraction, $file, $msg_body['field']);

            // Filter extraction
            $extraction->setDeduplicationType($msg_body['deduplication_type']);
            if (empty($msg_body['skip_extracting']))
            {
                $this->e_service->runExtraction($extraction);
            }
            $extraction->setIsDeduplicating(false);

            // Save changes
            $this->e_service->updateEntity($extraction);

            $this->e_service->logExtractionAction($extraction, 'Ending \'deduplication\', result: DONE');

            return true;
        }
        catch ( \Exception $e )
        {
            $this->e_service->logError($e);

            return false;
        }
    }
}