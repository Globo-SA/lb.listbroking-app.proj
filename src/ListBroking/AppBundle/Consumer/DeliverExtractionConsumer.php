<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Consumer;

use Doctrine\ORM\Mapping\ClassMetadata;
use ListBroking\AppBundle\Service\BusinessLogic\ExtractionServiceInterface;
use ListBroking\AppBundle\Service\Helper\AppServiceInterface;
use ListBroking\AppBundle\Service\Helper\FileHandlerServiceInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class DeliverExtractionConsumer implements ConsumerInterface
{

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

    function __construct (AppServiceInterface $a_service, ExtractionServiceInterface $e_service, FileHandlerServiceInterface $f_service)
    {
        $this->a_service = $a_service;
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
            $this->e_service->logExtractionAction($extraction, 'Starting \'deliverExtraction\'');

            $template = json_decode($this->e_service->findEntity('ListBrokingAppBundle:ExtractionTemplate', $msg_body['extraction_template_id'])
                                                    ->getTemplate(), 1);

            $query = $this->e_service->getExtractionContactsQuery($extraction, ClassMetadata::FETCH_EAGER);

            list($filepath, $password) = $this->f_service->generateFileFromQuery($extraction->getName(), $template['extension'], $query, $template['headers']);

            // Send the Extraction by Email
            $email_template = '@ListBrokingApp/KitEmail/deliver_extraction.html.twig';
            $email_subject = sprintf('LB Extraction - %s', $extraction->getName());
            $result = $this->a_service->deliverEmail($email_template, array('extraction' => $extraction, 'filepath' => $filepath, 'password' => $password), $email_subject, $msg_body['email']);

            $this->a_service->flushSpool();

            // Set the Extraction as delivered
            $extraction->setIsDelivering(false);

            // Save changes
            $this->e_service->updateEntity($extraction);

            $this->e_service->logExtractionAction($extraction, sprintf('Ending \'deliverExtraction\', email deliver result: %s to %s', $result ? 'YES' : 'NO', $msg_body['email']));

            return true;
        }
        catch ( \Exception $e )
        {
            $this->e_service->logError($e);

            return false;
        }
    }
}