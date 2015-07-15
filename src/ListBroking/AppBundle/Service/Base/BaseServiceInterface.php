<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */
namespace ListBroking\AppBundle\Service\Base;

use ListBroking\AppBundle\Exception\InvalidEntityTypeException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;

interface BaseServiceInterface
{

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
}