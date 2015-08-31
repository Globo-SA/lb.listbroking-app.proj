<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Consumer;

use ListBroking\AppBundle\Service\BusinessLogic\StagingServiceInterface;
use ListBroking\AppBundle\Service\Helper\FileHandlerServiceInterface;
use ListBroking\AppBundle\Service\Helper\MessagingServiceInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class StagingContactImportConsumer implements ConsumerInterface
{

    /**
     * @var MessagingServiceInterface
     */
    private $m_system;

    /**
     * @var StagingServiceInterface
     */
    private $s_service;

    /**
     * @var FileHandlerServiceInterface
     */
    private $f_service;

    public function __construct (MessagingServiceInterface $m_service, StagingServiceInterface $s_service, FileHandlerServiceInterface $f_service)
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

            $producer_id = MessagingServiceInterface::STAGING_CONTACT_IMPORT_PRODUCER;

            $msg_body = unserialize($msg->body);

            $this->s_service->logInfo(sprintf('Starting \'importStagingContacts\', filename: %s', $msg_body['filename']));

            $file = $this->f_service->import($msg_body['filename']);

            $this->s_service->importStagingContacts($file, array('owner' => $msg_body['owner'], 'update' => $msg_body['update']));

            $this->m_system->unlockProducer($producer_id);

            $this->s_service->logInfo('Ending \'importStagingContacts\'');

            return true;
        }
        catch ( \Exception $e )
        {
            $this->s_service->logError($e);

            return false;
        }
    }
}