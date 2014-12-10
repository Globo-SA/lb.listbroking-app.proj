<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\EventListener;


use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\Debug\Exception\FatalErrorException;

class FlushListener {

    /**
     * @var Cache
     */
    protected $dcache;

    function __construct(Cache $dcache)
    {
        $this->dcache = $dcache;
    }


    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->clearCache($entity);
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->clearCache($entity);

        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->clearCache($entity);

        }

        foreach ($uow->getScheduledCollectionDeletions() as $col) {
            $this->clearCache($col);

        }

        foreach ($uow->getScheduledCollectionUpdates() as $col) {
            $this->clearCache($col);

        }
    }

    /**
     * Clears list cache
     * @param $entity
     */
    private function clearCache($entity)
    {
        $const_name = get_class($entity) . "::CACHE_ID";
        if(defined($const_name)){
            die;
            $cache_id = $entity::CACHE_ID;
            $cache_id_array = $cache_id . '_array';

            $this->dcache->delete($cache_id);
            $this->dcache->delete($cache_id_array);

            if($entity){
                $this->dcache->delete($cache_id . "_{$entity->getId()}");
                $this->dcache->delete($cache_id_array . "_{$entity->getId()}");
            }
        }

    }
}