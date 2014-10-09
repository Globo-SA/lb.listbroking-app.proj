<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LockBundle\Service;


use ListBroking\CoreBundle\Service\BaseService;
use ListBroking\LeadBundle\Repository\ORM\ContactRepository;
use ListBroking\LeadBundle\Repository\ORM\LeadRepository;
use ListBroking\LockBundle\Engine\LockEngine;
use ListBroking\LockBundle\Repository\ORM\LockRepository;

/**
 * This service can not use a cache system, as locks are too volatile.
 * So every action should be a database call.
 * Class LockService
 * @package ListBroking\LockBundle\Service
 */
class LockService extends BaseService implements LockServiceInterface {

    private $lead_repo;
    private $lock_repo;
    private $engine;
    /**
     * @var ContactRepository
     */
    private $contact_repo;

    function __construct(LockRepository $lock_repo, LeadRepository $lead_repo, ContactRepository $contact_repo)
    {
        $this->lock_repo = $lock_repo;
        $this->lead_repo = $lead_repo;
        $this->contact_repo = $contact_repo;
    }

    /**
     * Gets list of locks
     * @param bool $only_active
     * @return mixed
     */
    public function getLockList($only_active = true){

        $entities = $this->lock_repo->findAll();

        return $entities;
    }

    /**
     * Gets a single lock
     * @param $id
     * @return mixed
     */
    public function getLock($id){
        $entity = $this->lock_repo->findOneById($id);

        return $entity;
    }

    /**
     * Adds a single lock
     * @param $lock
     * @return mixed
     */
    public function addLock($lock){

        // Create new entity
        $this->lock_repo->createNewEntity($lock);
        $this->lock_repo->flush();

        return $this;
    }

    /**
     * Removes a single lock
     * @param $id
     * @return mixed
     */
    public function removeLock($id){
        $entity = $this->hydrateObject($this->lock_repo, $id);
        $this->lock_repo->remove($entity);
        $this->lock_repo->flush();

        return $this;
    }

    /**
     * Removes expire locks
     * NOTE: Locks are always moved to a _log table
     * @param $days
     * @return int
     */
    public function removeExpiredLocks($days)
    {
        return $this->lock_repo->removeByExpirationDate($days);
    }

    /**
     * Updates a single country
     * @param $lock
     * @return mixed
     */
    public function updateLock($lock){
        $this->lock_repo->merge($lock);
        $this->lock_repo->flush();

        return $this;
    }

    /**
     * Gets an instance of the LockEngine
     * @return LockEngine
     */
    public function startEngine(){
        $this->engine = new LockEngine($this->lead_repo, $this->contact_repo);

        return $this->engine;
    }
}