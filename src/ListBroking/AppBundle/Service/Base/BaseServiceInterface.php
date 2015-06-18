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
     * @param Kernel $kernel
     */
    public function setKernel ($kernel);
    /**
     * @param $doctrine
     */
    public function setDoctrine($doctrine);

    /**
     * @param EntityManager $entityManager
     * @return mixed
     */
    public function setEntityManager(EntityManager $entityManager);

    /**
     * @param Cache $cache
     * @return mixed
     */
    public function setCache(Cache $cache);

    /**
     * @param FormFactory $formFactory
     * @return mixed
     */
    public function setFormFactory(FormFactory $formFactory);

    /**
     * @param Logger $logger
     */
    public function setLogger ($logger);

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
    public function getUser();

    /**
     * Clears the EntityManager
     * @return void
     */
    public function clearEntityManager();

    /**
     * Attaches an entity to the EntityManager
     * if needed
     * @param $entity
     * @return void
     */
    public function attach(&$entity);

    /**
     * Gets a List of Entities by type
     * @param $type
     * @param $hydrate
     * @internal param $cache_id
     * @return mixed
     */
    public function getEntities($type, $hydrate = true);

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
    public function getEntity($type, $id, $hydrate = true, $attach = false);

    /**
     * Adds a given entity
     * @param $type
     * @param $entity
     * @return mixed
     */
    public function addEntity($type, $entity);

    /**
     * Updates a given Entity
     * @param $type
     * @param $entity
     * @return mixed
     */
    public function updateEntity($type, $entity);

    /**
     * Removes a given Entity
     * @param $type
     * @param $entity
     * @return mixed
     */
    public function removeEntity($type, $entity);

    /**
     * Clears list cache
     * @param $entity
     * @param null $extra
     */
    public function clearCache($entity, $extra = null);

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
     * Get thrown Exceptions
     * @param $limit
     * @return mixed
     */
    public function getExceptions($limit);

    /**
     * Saves a file on a form
     * @param Form $form
     *
     * @return UploadedFile
     */
    public function saveFile(Form $form);
} 