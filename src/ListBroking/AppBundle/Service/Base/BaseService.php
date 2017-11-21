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

    const MODULE_LOCK_TTL = 60;

    /**
     * @var EntityManager
     */
    public $entityManager;

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
        $this->entityManager->clear();
        $this->entityManager->getConnection()->close();
        $this->entityManager->getConnection()->connect();
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
        $meta = $this->entityManager->getClassMetadata($repo_name);
        $class = new $meta->name;
        $cache_id = $class::CACHE_ID;

        // Check if there's cache
        if ( ! $this->doctrine_cache->contains($cache_id) )
        {
            $repo = $this->entityManager->getRepository($repo_name);
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
        $meta = $this->entityManager->getClassMetadata($repo_name);
        $class = new $meta->name;
        $cache_id = $class::CACHE_ID . '_' . $id;

        // Check if there's cache
        if ( ! $this->doctrine_cache->contains($cache_id) )
        {
            $repo = $this->entityManager->getRepository($repo_name);
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

        return $this->entityManager->getRepository('ListBrokingExceptionHandlerBundle:ExceptionLog')
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
        $this->entityManager->flush();
    }

    /**
     * @inheritdoc
     */
    public function lapStopWatch ($event_name)
    {
        $periods = $this->stopwatch->lap($event_name)
                                   ->getPeriods()
        ;

        return end($periods)->getDuration();
    }

    /**
     * @inheritdoc
     */
    public function logError ($msg)
    {
        $this->logger->error($this->generateLogMsg($msg));
    }

    /**
     * @inheritdoc
     */
    public function logInfo ($msg)
    {
        $this->logger->info($this->generateLogMsg($msg));
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
    public function setEntityManager ($entityManager)
    {
        $this->entityManager = $entityManager;
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
            $this->entityManager->flush();
        }
    }

    /**
     * @inheritDoc
     */
    public function lockExecution ($module)
    {
        $cache_id = $this->generateLockCacheId($module);
        if ( ! $this->doctrine_cache->contains($cache_id) )
        {
            $this->doctrine_cache->save($cache_id, true, self::MODULE_LOCK_TTL);
        }
    }

    /**
     * @inheritDoc
     */
    public function releaseExecution ($module)
    {
        $cache_id = $this->generateLockCacheId($module);
        if ( $this->doctrine_cache->contains($cache_id) )
        {
            $this->doctrine_cache->delete($cache_id);
        }
    }

    /**
     * @inheritDoc
     */
    public function isExecutionLocked ($module)
    {
        $cache_id = $this->generateLockCacheId($module);
        if ( $this->doctrine_cache->contains($cache_id) )
        {
            return true;
        }

        return false;
    }

    /**
     * Generates a cache_id to be used in the
     * module locking system
     *
     * @param $name
     *
     * @return string
     */
    private function generateLockCacheId ($name)
    {
        return sprintf("lock_cache_%s", $name);
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
        if ( $this->entityManager->getUnitOfWork()
                                 ->getEntityState($entity) == UnitOfWork::STATE_DETACHED
        )
        {
            $entity = $this->entityManager->merge($entity);
        }
    }

    /**
     * Generates a log message with date and memory usage
     *
     * @param string $msg
     *
     * @return string
     */
    private function generateLogMsg($msg){
        return sprintf("[%s][%s] %s", date('Y-m-d h:d:s') , $this->memoryUsage(), $msg);
    }
    /**
     * Returns the currently used memory
     *
     * @return string
     */
    private function memoryUsage()
    {
        $mem_usage = memory_get_usage(true);

        if ($mem_usage < 1024)
        {
            return $mem_usage . " B";
        }

        if ($mem_usage < 1048576)
        {
            return round($mem_usage / 1024, 2) . "kB";
        }

        return round($mem_usage / 1048576, 2) . "MB";
    }
}
