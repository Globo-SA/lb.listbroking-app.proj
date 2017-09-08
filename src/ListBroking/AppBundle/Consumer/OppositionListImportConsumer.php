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

class OppositionListImportConsumer implements ConsumerInterface
{
    const LOGGER_IDENTIFIER = 'RabbitMQ-OppositionListImportConsumer';

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

    /**
     * OppositionListImportConsumer constructor.
     *
     * @param MessagingServiceInterface         $m_service
     * @param StagingServiceInterface           $s_service
     * @param FileHandlerServiceInterface       $f_service
     * @param LoggerInterface                   $logger
     * @param ServiceLogTaskIdentifierInterface $serviceLogTaskIdentifier
     */
    public function __construct (
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
        $oppositionList = null;
        $clearOld       = null;
        $filename       = null;

        $producer_id = MessagingServiceInterface::OPPOSITION_LIST_IMPORT_PRODUCER;

        try {
            // PHP is run in shared nothing architecture, so long running processes need to
            // Clear the entity manager before running
            $this->s_service->clearEntityManager();

            $msg_body = unserialize($msg->body);

            $oppositionList = $msg_body['opposition_list'];
            $clearOld       = $msg_body['clear_old'];
            $filename       = $msg_body['filename'];

            $this->logger->info(
                'Starting "importOppositionList"',
                [
                    'opposition_list' => $oppositionList,
                    'clear_old'       => $clearOld,
                    'filename'        => $filename
                ]
            );

            $file = $this->f_service->loadExcelFile($filename);

            $this->s_service->importOppositionList($oppositionList, $file, $clearOld);

            $this->s_service->syncContactsWithOppositionLists();

            $this->m_system->unlockProducer($producer_id);

            $this->logger->info(
                'Ending "importOppositionList"',
                [
                    'opposition_list' => $oppositionList,
                    'clear_old'       => $clearOld,
                    'filename'        => $filename
                ]
            );
        }
        catch (\Exception $exception) {
            $this->m_system->unlockProducer($producer_id);

            $this->logger->error(
                '#LB-0018# Error on "importOppositionList"',
                [
                    'message'         => $exception->getMessage(),
                    'opposition_list' => $oppositionList,
                    'clear_old'       => $clearOld,
                    'filename'        => $filename
                ]
            );
        }

        return true;
    }
}
