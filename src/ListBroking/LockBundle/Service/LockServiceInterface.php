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


use ListBroking\LockBundle\Engine\LockEngine;

interface LockServiceInterface {

    /**
     * Gets list of locks
     * @param bool $only_active
     * @return mixed
     */
    public function getLockList($only_active = true);

    /**
     * Gets a single lock
     * @param $id
     * @return mixed
     */
    public function getLock($id);

    /**
     * Adds a single lock
     * @param $lock
     * @return mixed
     */
    public function addLock($lock);

    /**
     * Removes a single lock
     * @param $id
     * @return mixed
     */
    public function removeLock($id);

    /**
     * Removes expire locks
     * @param $days
     * @return mixed
     */
    public function removeExpiredLocks($days);

    /**
     * Updates a single country
     * @param $lock
     * @return mixed
     */
    public function updateLock($lock);

    /**
     * Gets an instance of the LockEngine
     * @return LockEngine
     */
    public function startEngine();
} 