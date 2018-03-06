<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Consumer;

use Adclick\Components\GDPR\Mailer\MailerGdpr;
use ListBroking\AppBundle\Entity\Extraction;
use Listbroking\AppBundle\Monolog\Processor\ServiceLogTaskIdentifierInterface;
use ListBroking\AppBundle\Service\BusinessLogic\ExtractionServiceInterface;
use ListBroking\AppBundle\Service\Helper\AppServiceInterface;
use ListBroking\AppBundle\Service\Helper\FileHandlerServiceInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class DeliverExtractionConsumer implements ConsumerInterface
{
    const LOGGER_IDENTIFIER = 'RabbitMQ-DeliverExtractionConsumer';

    /**
     * @var AppServiceInterface
     */
    private $a_service;

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
     * @var MailerGdpr
     */
    protected $mailer;

    /**
     * DeliverExtractionConsumer constructor.
     *
     * @param AppServiceInterface               $a_service
     * @param ExtractionServiceInterface        $e_service
     * @param FileHandlerServiceInterface       $f_service
     * @param LoggerInterface                   $logger
     * @param ServiceLogTaskIdentifierInterface $serviceLogTaskIdentifier
     * @param MailerGdpr                        $mailer
     */
    public function __construct(
        AppServiceInterface $a_service,
        ExtractionServiceInterface $e_service,
        FileHandlerServiceInterface $f_service,
        LoggerInterface $logger,
        ServiceLogTaskIdentifierInterface $serviceLogTaskIdentifier,
        MailerGdpr $mailer
    ) {
        $this->a_service = $a_service;
        $this->e_service = $e_service;
        $this->f_service = $f_service;
        $this->logger    = $logger;
        $this->mailer    = $mailer;

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
            $this->e_service->logExtractionAction($extraction, 'Starting "deliverExtraction"');
            $this->logger->info('Starting "deliverExtraction"', ['extraction_id' => $extraction->getId()]);

            $template = json_decode($this->e_service->findEntity('ListBrokingAppBundle:ExtractionTemplate', $msg_body['extraction_template_id'])
                                                    ->getTemplate(), 1);

            $batch_size = $this->e_service->findConfig("batch_sizes")['deliver'];

            list($path, $filename, $password) =  $this->e_service->exportExtractionContacts($this->f_service, $extraction, $template, $batch_size);

            $this->logger->info(
                'Exported contacts to file',
                ['extraction_id' => $extraction->getId(), 'path' => $path]
            );

            $this->mailer->sendAttachmentWithPassword(
                [$msg_body['email'] => $msg_body['email']],
                $extraction->getName(),
                $path,
                $filename,
                $password
            );

            $this->logger->info('Extraction email sent', ['extraction_id' => $extraction->getId()]);

            // Set the Extraction as delivered
            $extraction->setIsDelivering(false);

            // Save changes
            $this->e_service->updateEntity($extraction);

            $this->e_service->logExtractionAction(
                $extraction,
                sprintf('Ending "deliverExtraction", email processed to %s', $msg_body['email'])
            );

        } catch (\Exception $exception) {
            if ($extraction instanceof Extraction) {
                $this->e_service->logExtractionAction($extraction, sprintf('Error "deliverExtraction"'));

                // Set the Extraction as delivered
                $extraction->setIsDelivering(false);
                $this->e_service->updateEntity($extraction);
            }

            $this->logger->error(
                '#LB-0015# Error on "deliverExtraction"',
                [
                    'extraction_id' => $extraction instanceof Extraction ? $extraction->getId() : null,
                    'result'        => $exception->getMessage()
                ]
            );
        }

        return true;
    }
}
