<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\CoreBundle\Service;
use Adclick\CacheBundle\Manager\CacheManagerInterface;
use ListBroking\CoreBundle\Exception\EntityValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class BaseService {
    protected $cache;
    protected $validator;

    function __construct(CacheManagerInterface $cache, ValidatorInterface $validator)
    {
        $this->cache        = $cache;
        $this->validator    = $validator;
    }

    /**
     * Gets a List of entities
     * @param $list_name
     * @param $scope
     * @param $repo
     * @param bool $only_active
     * @return mixed|null
     */
    protected function getList($list_name, $scope, $repo, $only_active = false)
    {
        // Check if entity exists in cache
        if(!$this->cache->has($list_name, $scope)){
            $this->cache->beginWarmingUp($list_name, $scope);

            $entities = $repo->findAll();
            $this->cache->set($list_name, $entities, null, $scope);
        }
        $entities = $this->cache->get($list_name, $scope);

        foreach ($entities as $entity) {
            if (isset($entity['is_active']) && !$entity['is_active'] && $only_active){
                unset($entity);
            }
        }

        return $entities;
    }

    /**
     * Gets a single entity
     * @param $list_name
     * @param $scope
     * @param $repo
     * @param $id
     * @param $hydrate
     * @return mixed|null
     */
    protected function get($list_name, $scope, $repo,$id, $hydrate = false){
        // Check if entity exists in cache
        if (!$this->cache->has($list_name, $scope)){
            $this->cache->beginWarmingUp($list_name, $scope);

            $entities = $repo->findAll();

            $this->cache->set($list_name, $entities, null, $scope);
        }
        // Iterate through the cache and select correct entity by $id
        $entities = $this->cache->get($list_name, $scope);

        foreach ($entities as $entity) {
            if ($entity['id'] == $id){
                if($hydrate){
                    $entity = $this->hydrateObject($repo, $entity['id']);
                }
                return $entity;
            }
        }

        return null;
    }

    /**
     * Adds a single entity
     * @param $list_name
     * @param $scope
     * @param $repo
     * @param $entity
     */
    protected function add($list_name, $scope, $repo, $entity){
        // Create new entity
        $repo->createNewEntity($entity);
        $repo->flush();

        // Invalidate the cache
        if ($this->cache->has($list_name, $scope)){
            $this->cache->invalidateScope($scope);
        }
    }

    /**
     * Removes a single entity
     * @param $list_name
     * @param $scope
     * @param $repo
     * @param $id
     */
    protected function remove($list_name, $scope, $repo, $id){
        // Finds and removes the entity
        $entity = $repo->findOneById($id);
        $repo->remove($entity);
        $repo->flush();

        // Invalidate the cache
        if ($this->cache->has($list_name, $scope)){
            $this->cache->invalidateScope($scope);
        }
    }

    /**
     * Updates a single entity
     * @param $list_name
     * @param $scope
     * @param $repo
     * @param $entity
     */
    protected function update($list_name, $scope, $repo, $entity){
        if($this->cache->has($list_name,$scope)){
            $this->cache->invalidateScope($scope);
        }

        $repo->merge($entity);
        $repo->flush();
    }

    /**
     * Validates a single entity
     * @param $entity
     * @return bool
     * @throws EntityValidationException
     */
    protected function validateEntity($entity){
        $errors = $this->validator->validate($entity);
        if (count($errors) > 0){
            throw new EntityValidationException("Entity not valid. Reason: " . (string) $errors);
        }

        return true;
    }

    /**
     * Converts a single entity id to an object instance
     * Hydrates by id
     * @param $repo
     * @param $id
     */
    public function hydrateObject($repo, $id){
       return  $repo->getEntityManager()->getReference($repo->getEntityName(), $id);
    }
} 