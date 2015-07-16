<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Consumer;

use ListBroking\AppBundle\Service\BusinessLogic\StagingService;
use ListBroking\AppBundle\Service\Helper\FileHandlerService;
use ListBroking\AppBundle\Service\Helper\MessagingService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class OppositionListImportConsumer implements ConsumerInterface
{

    /**
     * @var MessagingService
     */
    private $m_system;

    /**
     * @var StagingService
     */
    private $s_service;

    /**
     * @var FileHandlerService
     */
    private $f_service;

    function __construct (MessagingService $m_service, StagingService $s_service, FileHandlerService $f_service)
    {
        $this->m_system = $m_service;
        $this->s_service = $s_service;
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
            $this->s_service->clearEntityManager();

            $producer_id = MessagingService::OPPOSITION_LIST_IMPORT_PRODUCER;

            $msg_body = unserialize($msg->body);

            $this->s_service->logInfo(sprintf("Starting 'importOppostionList' for opposition_list: %s", $msg_body['opposition_list']));

            $file = $this->f_service->import($msg_body['filename']);

            $this->s_service->importOppositionList($msg_body['opposition_list'], $file, $msg_body['clear_old']);

            $this->s_service->syncContactsWithOppositionLists();

            $this->m_system->unlockProducer($producer_id);

            $this->s_service->logInfo(sprintf("Ending 'importOppostionList' for opposition_list: %s, clear_old: %s, filename: %s", $msg_body['opposition_list'], $msg_body['clear_old'], $msg_body['filename']));

            return true;
        }
        catch ( \Exception $e )
        {
            $this->s_service->logError($e);

            return false;
        }
    }
}