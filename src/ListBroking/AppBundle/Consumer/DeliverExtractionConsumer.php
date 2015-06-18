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

class DeliverExtractionConsumer implements ConsumerInterface
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
            $this->e_service->logInfo(sprintf("Starting 'deliverExtraction' for extraction_id: %s", $msg_body['object_id']));

            /** @var Extraction $extraction */
            $extraction = $this->e_service->em->getRepository('ListBrokingAppBundle:Extraction')
                                              ->findOneBy(array(
                                                  'id' => $msg_body['object_id']
                                              ))
            ;

            /** @var Extraction $extraction */
            $filename = $this->e_service->exportExtraction($extraction, $this->e_service->getEntity('extraction_template', $msg_body['extraction_template_id']));
            $result = $this->e_service->deliverExtraction($extraction,'samuel.castro@adclick.pt', $filename);

            $extraction->setIsDelivering(false);

            // Save changes
            $this->e_service->updateEntity('extraction', $extraction);

            $this->e_service->logInfo(sprintf("Ending 'deliverExtraction' for extraction_id: %s, email sent (%s) to %s with the file %s", $msg_body['object_id'], $result, $msg_body['email'], $filename));
        }
        catch ( \Exception $e )
        {
            $this->e_service->logError($e);
            return false;
        }
    }
}