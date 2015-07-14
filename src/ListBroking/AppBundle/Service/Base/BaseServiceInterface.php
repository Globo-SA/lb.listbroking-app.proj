<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\Base;


use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use ListBroking\AppBundle\Exception\InvalidEntityTypeException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Tests\Logger;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

interface BaseServiceInterface {

    /**
     * Gets the App Root Dir
     * @return mixed|string
     */
    public function getRootDir();

    /**
     * @param TokenStorageInterface $token_storage
     */
    public function setTokenStorage ($token_storage);
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
     * Get Currently logged in user
     * @return mixed
     */
    public function findUser ();

    /**
     * Clears the EntityManager
     * @return void
     */
    public function clearEntityManager();

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
     * @param $repo_name
     * @param $id
     * @return mixed|object
     * @throws InvalidEntityTypeException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findEntity ($repo_name, $id);

    /**
     * Updates a given Entity
     * @param $entity
     * @return mixed
     */
    public function updateEntity($entity);

    /**
     * Flushes all database changes
     */
    public function flushAll();

    /**
     * Gets a configuration
     * @param $name
     * @return mixed
     */
    public function getConfig($name);

    /**
     * Generates a new form view
     * @param $type
     * @param bool $view
     * @param null $data
     * @param $action
     * @return FormBuilderInterface|Form
     */
    public function generateForm($type, $action = null, $data = null, $view = false);

    /**
     * Finds thrown Exceptions
     * @param $limit
     * @return mixed
     */
    public function findExceptions ($limit);
}