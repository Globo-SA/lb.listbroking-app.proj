<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Service\Helper;

use ListBroking\AppBundle\Exception\InvalidMessageException;
use ListBroking\AppBundle\Service\Base\BaseService;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MessagingService extends BaseService implements MessagingServiceInterface
{

    const SERVICE_BASE_NAME = 'old_sound_rabbit_mq.%s_producer';

    /**
     * @var ContainerInterface
     */
    private $container;

    function __construct (ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Publishes a new message to the Queue System
     *
     * @param $producer_id
     * @param $msg
     *
     * @return mixed
     */
    public function publishMessage ($producer_id, $msg)
    {
        $msg['type'] = $producer_id;

        $this->validateMessage($msg);

        /** @var Producer $producer */
        $producer = $this->container->get(sprintf(self::SERVICE_BASE_NAME, $producer_id));
        $producer->publish(serialize($msg));
    }

    /**
     * Validates if the message is correctly configured
     *
     * @param $msg
     *
     * @throws InvalidMessageException
     * @return void
     */
    private function validateMessage($msg){

        if(!is_array($msg)){
            throw new InvalidMessageException('Message should be an Array', 400);
        }

        if(!array_key_exists('type', $msg)){
            throw new InvalidMessageException('Message type must be present', 400);
        }

        if(empty($msg['type'])){
            throw new InvalidMessageException('Message type cannot be empty', 400);
        }

        if(!array_key_exists('object_id', $msg)){
            throw new InvalidMessageException('Message object_id must be present', 400);
        }

        if(empty($msg['object_id'])){
            throw new InvalidMessageException('Message object_id cannot be empty', 400);
        }
    }
}