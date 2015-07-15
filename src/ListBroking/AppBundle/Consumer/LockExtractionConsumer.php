<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Consumer;

use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Service\BusinessLogic\ExtractionService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class LockExtractionConsumer implements ConsumerInterface
{

    /**
     * @var ExtractionService
     */
    private $e_service;

    function __construct (ExtractionService $e_service)
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

            $this->e_service->logInfo(sprintf("Starting 'generateLocks' for extraction_id: %s", $msg_body['object_id']));

            /** @var Extraction $extraction */
            $extraction = $this->e_service->entity_manager->getRepository('ListBrokingAppBundle:Extraction')
                                                          ->findOneBy(array(
                                                              'id' => $msg_body['object_id']
                                                          ))
            ;
            // Generate locks
            $this->e_service->generateLocks($extraction, $msg_body['lock_types']);

            // Close extraction
            $extraction->setIsLocking(false);
            $extraction->setStatus(Extraction::STATUS_FINAL);

            // Save changes
            $this->e_service->updateEntity($extraction);

            $this->e_service->logInfo(sprintf("Ending 'generateLocks' for extraction_id: %s, result: Locks created", $msg_body['object_id']));

            return true;
        }
        catch ( \Exception $e )
        {
            $this->e_service->logError($e);

            return false;
        }
    }
}