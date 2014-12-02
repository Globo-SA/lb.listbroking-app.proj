<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service;


use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Entity\Category;
use ListBroking\AppBundle\Entity\Client;
use ListBroking\AppBundle\Entity\Country;
use ListBroking\AppBundle\Entity\County;
use ListBroking\AppBundle\Entity\District;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionDeduplication;
use ListBroking\AppBundle\Entity\ExtractionDeduplicationQueue;
use ListBroking\AppBundle\Entity\ExtractionTemplate;
use ListBroking\AppBundle\Entity\Gender;
use ListBroking\AppBundle\Entity\Owner;
use ListBroking\AppBundle\Entity\Parish;
use ListBroking\AppBundle\Entity\Source;
use ListBroking\AppBundle\Entity\SubCategory;
use ListBroking\AppBundle\Exception\InvalidEntityTypeException;

class AppService implements AppServiceInterface {

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Cache
     */
    private $dcache;

    function __construct(EntityManager $entityManager, Cache $doctrineCache)
    {
        $this->em = $entityManager;
        $this->dcache = $doctrineCache;
    }

    /**
     * Gets a List of Entities by type
     * @param $type
     * @param $hydrate
     * @internal param $cache_id
     * @return mixed
     */
    public function getEntities($type, $hydrate = true){

        // Entity information
        $entity_info = $this->getCacheIdAndRepo($type);
        $cache_id = $entity_info['cache_id'];
        $repo_name = $entity_info['repo_name'];

        $hydrate_mode = AbstractQuery::HYDRATE_OBJECT;
        if(!$hydrate){
            $cache_id .= '_array';
            $hydrate_mode = AbstractQuery::HYDRATE_ARRAY;
        }

        // Check if there's cache
        if(!$this->dcache->contains($cache_id)){
            $repo = $this->em->getRepository($repo_name);
            $entities = $repo->createQueryBuilder('e')->getQuery()->getResult($hydrate_mode);
            $this->dcache->save($cache_id, $entities);
        }

        return $this->dcache->fetch($cache_id);
    }

    /**
     * Gets and entity by type and id
     * @param $type
     * @param $id
     * @param bool $hydrate
     * @param bool $attach
     * @return mixed|object
     * @throws InvalidEntityTypeException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEntity($type, $id, $hydrate = true, $attach = false){

        // Entity information
        $entity_info = $this->getCacheIdAndRepo($type);
        $cache_id = $entity_info['cache_id'] . "_{$id}";
        $repo_name = $entity_info['repo_name'];
        $hydrate_mode = AbstractQuery::HYDRATE_OBJECT;
        if(!$hydrate){
            $cache_id .= '_array';
            $hydrate_mode = AbstractQuery::HYDRATE_ARRAY;
        }

        // Check if there's cache
        if(!$this->dcache->contains($cache_id) || $attach){
            $repo = $this->em->getRepository($repo_name);
            $entity = $repo->createQueryBuilder('e')
                ->where('e = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult($hydrate_mode);
            if($entity){
                $this->dcache->save($cache_id, $entity);
            }
            // Return the attached entity
            if($attach){
                return $entity;
            }
        }

        // Fetch from cache
        $entity = $this->dcache->fetch($cache_id);

        return $entity;
    }

    /**
     * Adds a given entity
     * @param $entity
     * @return mixed
     */
    public function addEntity($entity)
    {
        if($entity)
        {
            $this->em->persist($entity);
            $this->em->flush();
        }
    }

    /**
     * Updates a given Entity
     * @param $entity
     * @return mixed
     */
    public function updateEntity($entity)
    {
        if($entity)
        {
            $cache_id = $entity::CACHE_ID . "_{$entity->getId()}";
            // If cache exists, the entity needs to
            // be attached to the EntityManager
            if($this->dcache->contains($cache_id)){
                //TODO: Something is strange here, $entity is not used?
                $entity = $this->em->merge($entity);
            }
            $this->em->flush();

            // Clear cache
            $this->dcache->delete($cache_id);
        }
    }

    /**
     * Removes a given Entity
     * @param $entity
     * @return mixed
     */
    public function removeEntity($entity)
    {
        if($entity){
            $cache_id = $entity::CACHE_ID . "_{$entity->getId()}";

            // If cache exists, the entity needs to
            // be attached to the EntityManager
            if($this->dcache->contains($cache_id)){
                $entity = $this->em->merge($entity);
            }

            // Remove entity
            $this->em->remove($entity);
            $this->em->flush();

            // Clear cache
            $this->dcache->delete($cache_id);
        }
    }

    /**
     * @param $code
     * @param bool $hydrate
     * @return mixed
     */
    public function getCountryByCode($code, $hydrate = true)
    {
       $entities = $this->getEntities('country', $hydrate);
        foreach ($entities as $entity){
            if($hydrate && $entity->getIsoCode() == $code){
                return $entity;
            }elseif($entity['iso_code'] == $code){
                return $entity;
            }
        }

        return null;
    }

    /**
     * Gets a Entity type cache and repo info
     * @param $type
     * @throws InvalidEntityTypeException
     */
    private function getCacheIdAndRepo($type){

        switch($type){
            case 'client':
                return array(
                    'cache_id' => Client::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:Client'
                );
                break;
            case 'campaign':
                return array(
                    'cache_id' => Campaign::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:Campaign'
                );
                break;
            case 'category':
                return array(
                    'cache_id' => Category::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:Category'
                );
                break;
            case 'sub_category':
                return array(
                    'cache_id' => SubCategory::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:SubCategory'
                );
                break;
            case 'country':
                return array(
                    'cache_id' => Country::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:Country'
                );
                break;
            case 'extraction':
                return array(
                    'cache_id' => Extraction::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:Extraction'
                );
                break;
            case 'extraction_template':
                return array(
                    'cache_id' => ExtractionTemplate::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:ExtractionTemplate'
                );
                break;
            case 'extraction_deduplication':
                return array(
                    'cache_id' => ExtractionDeduplication::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:ExtractionDeduplication'
                );
                break;
            case 'extraction_deduplication_queue':
                return array(
                    'cache_id' => ExtractionDeduplicationQueue::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:ExtractionDeduplicationQueue'
                );
                break;
            case 'gender':
                return array(
                    'cache_id' => Gender::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:Gender'
                );
                break;
            case 'district':
                return array(
                    'cache_id' => District::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:District'
                );
                break;
            case 'county':
                return array(
                    'cache_id' => County::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:County'
                );
                break;
            case 'parish':
                return array(
                    'cache_id' => Parish::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:Parish'
                );
                break;
            case 'owner':
                return array(
                    'cache_id' => Owner::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:Owner'
                );
                break;
            case 'source':
                return array(
                    'cache_id' => Source::CACHE_ID,
                    'repo_name' => 'ListBrokingAppBundle:Source'
                );
                break;
            default:
                throw new InvalidEntityTypeException("The Entity type {$type} is invalid.");
                break;
        }
    }
} 