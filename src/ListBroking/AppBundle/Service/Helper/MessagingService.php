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

class MessagingService implements MessagingServiceInterface
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

    public function publishMessage ($producer_id, $msg)
    {
        $msg['type'] = $producer_id;

        /** @var Producer $producer */
        $producer = $this->container->get(sprintf(self::SERVICE_BASE_NAME, $producer_id));
        $producer->publish(serialize($msg));
    }
}