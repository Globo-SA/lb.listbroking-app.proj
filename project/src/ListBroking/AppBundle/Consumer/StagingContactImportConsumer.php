<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Consumer;

use ListBroking\AppBundle\Monolog\Processor\ServiceLogTaskIdentifierInterface;
use ListBroking\AppBundle\Service\BusinessLogic\StagingServiceInterface;
use ListBroking\AppBundle\Service\Helper\FileHandlerServiceInterface;
use ListBroking\AppBundle\Service\Helper\MessagingServiceInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class StagingContactImportConsumer implements ConsumerInterface
{
    const LOGGER_IDENTIFIER = 'RabbitMQ-StagingContactImportConsumer';

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

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        MessagingServiceInterface $m_service,
        StagingServiceInterface $s_service,
        FileHandlerServiceInterface $f_service,
        LoggerInterface $logger,
        ServiceLogTaskIdentifierInterface $serviceLogTaskIdentifier
    ) {
        $this->m_system  = $m_service;
        $this->s_service = $s_service;
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
        $owner    = null;
        $update   = null;
        $filename = null;

        $producer_id = MessagingServiceInterface::STAGING_CONTACT_IMPORT_PRODUCER;

        try {
            // PHP is run in shared nothing architecture, so long running processes need to
            // Clear the entity manager before running
            $this->s_service->clearEntityManager();

            $msg_body = unserialize($msg->body);

            $owner    = $msg_body['owner'];
            $update   = $msg_body['update'];
            $filename = $msg_body['filename'];

            $this->logger->info(
                'Starting "importStagingContacts"',
                [
                    'owner'    => $owner,
                    'update'   => $update,
                    'filename' => $filename
                ]
            );

            $file = $this->f_service->loadExcelFile($msg_body['filename']);

            $batch_size = $this->s_service->findConfig("batch_sizes")['staging_import'];

            $this->s_service->importStagingContacts(
                $file,
                $batch_size,
                [
                    'owner'      => $owner,
                    'for_update' => $update ? 1 : 0
                ]
            );

            $this->m_system->unlockProducer($producer_id);

            $this->logger->info(
                'Ending "importStagingContacts"',
                [
                    'owner'    => $owner,
                    'update'   => $update,
                    'filename' => $filename
                ]
            );
        } catch (\Exception $exception) {
            $this->m_system->unlockProducer($producer_id);

            $this->logger->info(
                'Error "importStagingContacts"',
                [
                    'owner'    => $owner,
                    'update'   => $update,
                    'filename' => $filename,
                    'message'  => $exception->getMessage()
                ]
            );
        }

        return true;
    }
}
