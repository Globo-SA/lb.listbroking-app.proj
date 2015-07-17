<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Consumer;

use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Service\BusinessLogic\ExtractionServiceInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class DeduplicateExtractionConsumer implements ConsumerInterface
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
            // PHP is run in shared nothing architecture, so long running processes need to
            // Clear the entity manager before running
            $this->e_service->clearEntityManager();

            $msg_body = unserialize($msg->body);

            $this->e_service->logInfo(sprintf("Starting 'deduplication' for extraction_id: %s with field: %s and file: %s", $msg_body['object_id'], $msg_body['field'], $msg_body['filename']));

            /** @var Extraction $extraction */
            $extraction = $this->e_service->entity_manager->getRepository('ListBrokingAppBundle:Extraction')
                                                          ->findOneBy(array(
                                                              'id' => $msg_body['object_id']
                                                          ))
            ;

            $filename = $msg_body['filename'];

            // Persist deduplications to the DB
            $this->e_service->uploadDeduplicationsByFile($extraction, $filename, $msg_body['field']);

            // Filter extraction
            $extraction->setDeduplicationType($msg_body['deduplication_type']);
            $this->e_service->runExtraction($extraction);
            $extraction->setIsDeduplicating(false);

            // Save changes
            $this->e_service->updateEntity($extraction);

            // Delete file
            unlink($filename);

            $this->e_service->logInfo(sprintf("Ending 'deduplication' for extraction_id: %s, result: DONE", $msg_body['object_id']));

            return true;
        }
        catch ( \Exception $e )
        {
            $this->e_service->logError($e);

            return false;
        }
    }
}