<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\Base;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\UnitOfWork;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class BaseService implements BaseServiceInterface
{

    // Cache TTL of 12h
    const CACHE_TTL = 43200;

    /**
     * @var EntityManager
     */
    public $entity_manager;

    /**
     * @var Cache
     */
    protected $doctrine_cache;

    /**
     * @var FormFactory
     */
    protected $form_factory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var TokenStorageInterface
     */
    protected $token_storage;

    public function clearEntityManager ()
    {
        $this->entity_manager->clear();
    }

    public function findConfig ($name)
    {
        $entities = $this->findEntities('ListBrokingAppBundle:Configuration');
        foreach ( $entities as $entity )
        {
            if ( $entity['name'] == $name )
            {
                return $entity['value'];
            }
        }

        return null;
    }

    public function findEntities ($repo_name)
    {
        // Get Cache ID
        $meta = $this->entity_manager->getClassMetadata($repo_name);
        $class = new $meta->name;
        $cache_id = $class::CACHE_ID;

        // Check if there's cache
        if ( ! $this->doctrine_cache->contains($cache_id) )
        {
            $repo = $this->entity_manager->getRepository($repo_name);
            $entities = $repo->createQueryBuilder('e')
                             ->getQuery()
                             ->getResult(Query::HYDRATE_ARRAY)
            ;

            if ( $entities )
            {
                $this->doctrine_cache->save($cache_id, $entities, self::CACHE_TTL);
            }
        }

        // Fetch from cache
        return $this->doctrine_cache->fetch($cache_id);
    }

    public function findEntity ($repo_name, $id)
    {
        // Get Cache ID
        $meta = $this->entity_manager->getClassMetadata($repo_name);
        $class = new $meta->name;
        $cache_id = $class::CACHE_ID . '_' . $id;

        // Check if there's cache
        if ( ! $this->doctrine_cache->contains($cache_id) )
        {
            $repo = $this->entity_manager->getRepository($repo_name);
            $entity = $repo->find($id);
            if ( $entity )
            {
                $this->doctrine_cache->save($cache_id, $entity, self::CACHE_TTL);
            }
        }

        // Fetch from cache
        return $this->doctrine_cache->fetch($cache_id);
    }

    public function findExceptions ($limit)
    {

        return $this->entity_manager->getRepository('ListBrokingExceptionHandlerBundle:ExceptionLog')
                        ->findLastExceptions($limit)
            ;
    }

    public function findUser ()
    {
        return $this->token_storage->getToken()
                                   ->getUser()
            ;
    }

    public function flushAll ()
    {
        $this->entity_manager->flush();
    }

    public function logError ($msg)
    {
        $msg = '[' . date('Y-m-d h:d:s') . '] ' . $msg;
        $this->logger->error($msg);
    }

    public function logInfo ($msg)
    {

        $msg = '[' . date('Y-m-d h:d:s') . '] ' . $msg;
        $this->logger->info($msg);
    }

    public function updateEntity ($entity)
    {
        if ( $entity )
        {
            $this->attachToEntityManager($entity);
            $this->entity_manager->flush();
        }
    }

    /**
     * Attaches an entity to the EntityManager
     * if needed
     *
     * @param $entity
     *
     * @return void
     */
    private function attachToEntityManager (&$entity)
    {
        if ( $this->entity_manager->getUnitOfWork()
                      ->getEntityState($entity) == UnitOfWork::STATE_DETACHED
        )
        {
            $entity = $this->entity_manager->merge($entity);
        }
    }

    /**
     * @param Cache $doctrine_cache
     */
    public function setDoctrineCache ($doctrine_cache)
    {
        $this->doctrine_cache = $doctrine_cache;
    }

    /**
     * @param EntityManager $entity_manager
     */
    public function setEntityManager ($entity_manager)
    {
        $this->entity_manager = $entity_manager;
    }

    /**
     * @param FormFactory $form_factory
     */
    public function setFormFactory ($form_factory)
    {
        $this->form_factory = $form_factory;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger ($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param TokenStorageInterface $token_storage
     */
    public function setTokenStorage ($token_storage)
    {
        $this->token_storage = $token_storage;
    }
}