<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Service\Helper;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MessagingService implements MessagingServiceInterface
{

    private $doctrine_cache;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct (ContainerInterface $container)
    {
        $this->container = $container;
        $this->doctrine_cache = $container->get('doctrine_cache.providers.memcached_query_cache');
    }

    /**
     * @inheritdoc
     */
    public function publishMessage ($producer_id, $msg)
    {
        $msg['type'] = $producer_id;

        /** @var Producer $producer */
        $producer = $this->container->get(sprintf(self::SERVICE_BASE_NAME, $producer_id));
        $producer->publish(serialize($msg));
    }

    /**
     * @inheritdoc
     */
    public function isProducerLocked ($name)
    {
        return $this->doctrine_cache->contains($name);
    }

    /**
     * @inheritdoc
     */
    public function lockProducer ($name)
    {
        $this->doctrine_cache->save($name, true);
    }

    /**
     * @inheritdoc
     */
    public function unlockProducer ($name)
    {
        $this->doctrine_cache->delete($name);
    }
}