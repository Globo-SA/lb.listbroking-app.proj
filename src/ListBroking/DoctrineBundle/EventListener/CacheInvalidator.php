<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\DoctrineBundle\EventListener;


use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * NOT IN USE
 * Class CacheInvalidator
 * @package ListBroking\DoctrineBundle\EventListener
 */
class CacheInvalidator {

    protected $cache_ids = array();

    function __construct($cache_ids)
    {
        $this->cache_ids = $cache_ids;
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();

        /** @var UnitOfWork $uow */
        $uow = $em->getUnitOfWork();

        // The UnitOfWork is responsible for tracking changes to
        // objects during an "object-level" transaction and for
        // writing out changes to the database in the correct order.
        $scheduled_entity_changes = array(
            'insert' => $uow->getScheduledEntityInsertions(),
            'update' => $uow->getScheduledEntityUpdates(),
            'delete' => $uow->getScheduledEntityDeletions()
        );

        foreach ($scheduled_entity_changes as $change => $entities){
            foreach ($entities as $entity){
                $this->cache_ids = array_merge($this->cache_ids, $this->getCacheIdsForEntity($entity, $change));
            }

        }

        if(count($this->cache_ids) == 0){
            return;
        }

        $this->cache_ids = array_unique($this->cache_ids);

        $result_cache = $em->getConfiguration()->getResultCacheImpl();
        array_map(array($result_cache, 'delete'), $this->cache_ids);
    }

    public function getCacheIdsForEntity($entity, $change){

        $class_name = get_class($entity);
        if(!array_key_exists($class_name, $this->cache_ids)){
            return array();
        }

        $parsed_cache_ids = array();
        foreach ($this->cache_ids[$class_name] as $cache_id){
            if(!isset($cache_id['cache'])){
                $cache_id['change'] = 'any';
            }

            $cache_id['change'] = strtolower($cache_id['change']);
            if ($cache_id['change'] != $change && $cache_id['change'] != 'any') {
                continue;
            }

            if ($parsedId = $this->parseCacheId($cache_id, $entity)) {
                $parsed_cache_ids[] = $parsedId;
            }
            unset($parsedId);

        }

        return $parsed_cache_ids;
    }

    public function parseCacheId(array $cache_id, $entity){
        if(!array_key_exists('id', $cache_id)){
            return false;
        }

        if(!array_key_exists('vars', $cache_id)){
            return $cache_id['id'];
        }

        $parsed_vars = array();
        foreach ($cache_id['vars'] as $var){
            if(!isset($var['value'])){
                continue;
            }

            $parsed_vars[] = $this->resolveVar($var['value'], $var['type'], $entity);
        }

        return vsprintf($cache_id['id'], $parsed_vars);
    }

    public function resolveVar($value, $type, $entity){
        if($type != 'method'){
            return $value;
        }

        $method_str = $value;
        $check_entity_method = function($obj, $method) use ($method_str) {
            if(!method_exists($obj, $method)) {
                throw new Exception(sprintf('%s is not a valid method', $method_str));
            }

            return $obj->{$method}();
        };

        if (!strstr($method_str, '.')) {
            return $check_entity_method($entity, $method_str);
        }

        $methods = explode('.', $method_str);
        $numMethods = count($methods);

        $resolvedValue = $entity;

        for ($i = 0; $i < $numMethods; $i++) {
            $resolvedValue = $check_entity_method($resolvedValue, $methods[$i]);
        }

        return $resolvedValue;
    }

    protected function getCacheIds(){
        return array(
            'Keleko\\Test\\Entity\\BlogPost' => array(
                array(
                    'id' => 'posts_for_user_%d',
                    'vars' => array(
                        array(
                            'value' => 'getUser',
                            'type' => 'method',
                        ),
                    ),
                    'change' => 'any',
                ),
            ),

            // Add more entities etc here
        );
    }
}