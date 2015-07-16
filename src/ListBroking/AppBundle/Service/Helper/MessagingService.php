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

    private $doctrine_cache;

    const SERVICE_BASE_NAME = 'old_sound_rabbit_mq.%s_producer';

    const OPPOSITION_LIST_IMPORT_PRODUCER = 'opposition_list_import';

    const STAGING_CONTACT_IMPORT_PRODUCER = 'staging_contact_import';

    /**
     * @var ContainerInterface
     */
    private $container;

    function __construct (ContainerInterface $container)
    {
        $this->container = $container;
        $this->doctrine_cache = $container->get('doctrine_cache.providers.memcached_query_cache');
    }

    public function publishMessage ($producer_id, $msg)
    {
        $msg['type'] = $producer_id;

        /** @var Producer $producer */
        $producer = $this->container->get(sprintf(self::SERVICE_BASE_NAME, $producer_id));
        $producer->publish(serialize($msg));
    }

    /**
     * Check if a RabbitMQ Producer is Locked
     *
     * @param $name
     *
     * @return mixed
     */
    public function isProducerLocked($name)
    {
        return $this->doctrine_cache->contains($name);
    }

    /**
     * Lock Producer from getting new items
     * @param $name
     *
     * @return mixed
     */
    public function lockProducer($name)
    {
        $this->doctrine_cache->save($name, true);
    }

    /**
     * Unlock Producer
     * @param $name
     *
     * @return mixed
     */
    public function unlockProducer($name)
    {
        $this->doctrine_cache->delete($name);
    }
}