<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\Base;

use Doctrine\ORM\Query;
use Doctrine\ORM\UnitOfWork;
use ListBroking\AppBundle\Entity\Configuration;
use ListBroking\AppBundle\Exception\InvalidEntityTypeException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;

abstract class BaseService extends AbstractBaseService implements BaseServiceInterface
{

    // Cache TTL of 12h
    const CACHE_TTL = 43200;

    /**
     * Flushes all database changes
     */
    public function flushAll ()
    {
        $this->em->flush();
    }

    /**
     * Clears the EntityManager
     * @return void
     */
    public function clearEntityManager ()
    {
        $this->em->clear();
    }

    /**
     * Updates a given Entity
     *
     * @param $entity
     *
     * @return mixed
     */
    public function updateEntity ($entity)
    {
        if ( $entity )
        {
            $this->attachToEntityManager($entity);
            $this->em->flush();
        }
    }

    /**
     * Finds a List of Entities by type
     *
     * @param $repo_name
     *
     * @return mixed
     */
    public function findEntities ($repo_name)
    {
        // Get Cache ID
        $meta = $this->em->getClassMetadata($repo_name);
        $class = new $meta->name;
        $cache_id = $class::CACHE_ID;

        // Check if there's cache
        if ( ! $this->dcache->contains($cache_id) )
        {
            $repo = $this->em->getRepository($repo_name);
            $entities = $repo->createQueryBuilder('e')
                             ->getQuery()
                             ->getResult(Query::HYDRATE_ARRAY)
            ;

            if ( $entities )
            {
                $this->dcache->save($cache_id, $entities, self::CACHE_TTL);
            }
        }

        // Fetch from cache
        return $this->dcache->fetch($cache_id);
    }

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
    public function findEntity ($repo_name, $id)
    {
        // Get Cache ID
        $meta = $this->em->getClassMetadata($repo_name);
        $class = new $meta->name;
        $cache_id = $class::CACHE_ID . '_' . $id;

        // Check if there's cache
        if ( ! $this->dcache->contains($cache_id) )
        {
            $repo = $this->em->getRepository($repo_name);
            $entity = $repo->find($id)
            ;
            if ( $entity )
            {
                $this->dcache->save($cache_id, $entity, self::CACHE_TTL);
            }
        }

        // Fetch from cache
        return $this->dcache->fetch($cache_id);
    }

    /**
     * Finds thrown Exceptions
     *
     * @param $limit
     *
     * @return mixed
     */
    public function findExceptions ($limit)
    {

        return $this->em->getRepository('ListBrokingExceptionHandlerBundle:ExceptionLog')
                        ->findLastExceptions($limit)
            ;
    }

    /**
     * Get Currently logged in user
     * @return mixed
     */
    public function findUser ()
    {
        return $this->token_storage->getToken()
                                   ->getUser()
            ;
    }

    /**
     * Generates a new form view
     *
     * @param      $type
     * @param bool $view
     * @param null $data
     * @param      $action
     *
     * @return FormBuilderInterface|Form
     */
    public function generateForm ($type, $action = null, $data = null, $view = false)
    {
        $form = $this->form_factory->createBuilder($type, $data);
        if ( $action )
        {
            $form->setAction($action);
        }
        if ( $view )
        {
            return $form->getForm()
                        ->createView()
                ;
        }

        return $form->getForm();
    }

    /**
     * Gets a configuration
     *
     * @param $name
     *
     * @return mixed
     */
    public function getConfig ($name)
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

    /**
     * Gets the App Root Dir
     * @return mixed|string
     */
    public function getRootDir ()
    {
        return $this->kernel->getRootDir();
    }

    /**
     * Log an error to the channel
     *
     * @param $msg
     */
    public function logError ($msg)
    {
        $msg = '[' . date('Y-m-d h:d:s') . '] ' . $msg;
        $this->logger->error($msg);
    }

    /**
     * Log an info to the channel
     *
     * @param $msg
     */
    public function logInfo ($msg)
    {

        $msg = '[' . date('Y-m-d h:d:s') . '] ' . $msg;
        $this->logger->info($msg);
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
        if ( $this->em->getUnitOfWork()
                      ->getEntityState($entity) == UnitOfWork::STATE_DETACHED
        )
        {
            $entity = $this->em->merge($entity);
        }
    }
}