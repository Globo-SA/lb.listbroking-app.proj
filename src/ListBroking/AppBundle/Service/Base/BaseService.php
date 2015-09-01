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
use Symfony\Component\Stopwatch\Stopwatch;

abstract class BaseService implements BaseServiceInterface
{

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

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @inheritdoc
     */
    public function clearEntityManager ()
    {
        $this->entity_manager->clear();
    }

    /**
     * @inheritdoc
     */
    public function findConfig ($name)
    {
        $entities = $this->findEntities('ListBrokingAppBundle:Configuration');
        foreach ( $entities as $entity )
        {
            if ( $entity['name'] == $name )
            {
                if ( $entity['type'] == 'json' )
                {
                    $entity['value'] = json_decode($entity['value'], 1);
                }

                return $entity['value'];
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function findExceptions ($limit)
    {

        return $this->entity_manager->getRepository('ListBrokingExceptionHandlerBundle:ExceptionLog')
                                    ->findLastExceptions($limit)
            ;
    }

    /**
     * @inheritdoc
     */
    public function findUser ()
    {
        return $this->token_storage->getToken()
                                   ->getUser()
            ;
    }

    /**
     * @inheritdoc
     */
    public function flushAll ()
    {
        $this->entity_manager->flush();
    }

    /**
     * @inheritdoc
     */
    public function lapStopWatch ($event_name)
    {
        $periods = $this->stopwatch->lap($event_name)->getPeriods();
        return end($periods)->getDuration();
    }

    /**
     * @inheritdoc
     */
    public function logError ($msg)
    {
        $msg = '[' . date('Y-m-d h:d:s') . '] ' . $msg;
        $this->logger->error($msg);
    }

    /**
     * @inheritdoc
     */
    public function logInfo ($msg)
    {

        $msg = '[' . date('Y-m-d h:d:s') . '] ' . $msg;
        $this->logger->info($msg);
    }

    /**
     * @inheritdoc
     */
    public function setDoctrineCache ($doctrine_cache)
    {
        $this->doctrine_cache = $doctrine_cache;
    }

    /**
     * @inheritdoc
     */
    public function setEntityManager ($entity_manager)
    {
        $this->entity_manager = $entity_manager;
    }

    /**
     * @inheritdoc
     */
    public function setFormFactory ($form_factory)
    {
        $this->form_factory = $form_factory;
    }

    /**
     * @inheritdoc
     */
    public function setLogger ($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function setTokenStorage ($token_storage)
    {
        $this->token_storage = $token_storage;
    }

    /**
     * @inheritdoc
     */
    public function startStopWatch ($event_name)
    {
        $this->stopwatch = new Stopwatch();

        return $this->stopwatch->start($event_name);
    }

    /**
     * @inheritdoc
     */
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
}