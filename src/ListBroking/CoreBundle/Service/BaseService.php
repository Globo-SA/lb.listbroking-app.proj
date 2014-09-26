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
     * @param $list_name
     * @param $scope
     * @param $repo
     * @param bool $only_active
     * @return mixed|null
     */
    protected function getList($list_name, $scope, $repo, $only_active = true)
    {
        if(!$this->cache->has($list_name, $scope)){
            $this->cache->beginWarmingUp($list_name, $scope);

            $entities = $repo->findAll();
            $this->cache->set($list_name, $entities, null, $scope);
        }

        $entities = $this->cache->get($list_name, $scope);
        foreach ($entities as $entity) {
            if (!$entity->getIsActive() && $only_active){
                unset($entity);
            }
        }

        return $entities;
    }

    /**
     * @param $list_name
     * @param $scope
     * @param $repo
     * @param $id
     * @return null
     */
    protected function get($list_name, $scope, $repo, $id){
        // Check if entity exists in cache
        if (!$this->cache->has($list_name, $scope)){
            $this->cache->beginWarmingUp($list_name, $scope);

            $entities = $repo->findAll();

            $this->cache->set($list_name, $entities, null, $scope);
        }
        // Iterate through the cache and select correct country by $id
        $entities = $this->cache->get($list_name, $scope);

        foreach ($entities as $entity) {
            if ($entity['id'] == $id){
                return $entity;
            }
        }

        return null;
    }

    /**
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
     * @param $list_name
     * @param $scope
     * @param $repo
     * @param $id
     */
    protected function remove($list_name, $scope, $repo, $id){
        // Finds and removes the country
        $country = $repo->findOneById($id);
        $repo->remove($country);
        $repo->flush();

        // Invalidate the cache
        if ($this->cache->has($list_name, $scope)){
            $this->cache->invalidateScope($scope);
        }
    }

    /**
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
} 