<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */
namespace ListBroking\AppBundle\Service\Base;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use ListBroking\AppBundle\Exception\InvalidEntityTypeException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Stopwatch\Stopwatch;

interface BaseServiceInterface
{

    // Cache TTL of 12h
    const CACHE_TTL = 43200;

    /**
     * Flushes all database changes
     */
    public function flushAll ();

    /**
     * Clears the EntityManager
     * @return void
     */
    public function clearEntityManager ();

    /**
     * Updates a given Entity
     *
     * @param $entity
     *
     * @return mixed
     */
    public function updateEntity ($entity);

    /**
     * Finds a List of Entities by type
     *
     * @param $repo_name
     *
     * @return mixed
     */
    public function findEntities ($repo_name);

    /**
     * Finds an Entity by type and id
     *
     * @param $repo_name
     * @param $id
     *
     * @return mixed|object
     * @throws InvalidEntityTypeException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findEntity ($repo_name, $id);

    /**
     * Finds thrown Exceptions
     *
     * @param $limit
     *
     * @return mixed
     */
    public function findExceptions ($limit);

    /**
     * Get Currently logged in user
     * @return mixed
     */
    public function findUser ();

    /**
     * Gets a configuration
     *
     * @param $name
     *
     * @return mixed
     */
    public function findConfig ($name);

    /**
     * Log an error to the channel
     *
     * @param $msg
     */
    public function logError ($msg);

    /**
     * Log an info to the channel
     *
     * @param $msg
     */
    public function logInfo ($msg);

    /**
     * Starts a new StopWatch instance
     *
     * @param $event_name
     *
     * @return Stopwatch
     */
    public function startStopWatch ($event_name);

    /**
     * Laps the current StopWatch and returns the lap duration
     * @param $event_name
     *
     * @return mixed
     */
    public function lapStopWatch($event_name);

    /**
     * Locks the Execution of a given module
     *
     * @param $module string
     *
     * @return void
     */
    public function lockExecution($module);

    /**
     * Releases the Lock on a given module
     *
     * @param $module string
     *
     * @return void
     */
    public function releaseExecution($module);

    /**
     * Checks if a module is locked
     *
     * @param $module string
     *
     * @return boolean
     */
    public function isExecutionLocked($module);

    /**
     * @param Cache $doctrine_cache
     */
    public function setDoctrineCache ($doctrine_cache);

    /**
     * @param EntityManager $entity_manager
     */
    public function setEntityManager ($entity_manager);

    /**
     * @param FormFactory $form_factory
     */
    public function setFormFactory ($form_factory);

    /**
     * @param Logger $logger
     */
    public function setLogger ($logger);

    /**
     * @param TokenStorageInterface $token_storage
     */
    public function setTokenStorage ($token_storage);
}