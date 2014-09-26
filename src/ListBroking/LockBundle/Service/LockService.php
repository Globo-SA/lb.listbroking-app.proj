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


use Adclick\CacheBundle\Manager\CacheManagerInterface;
use ListBroking\CoreBundle\Service\BaseService;
use ListBroking\LockBundle\Repository\LockRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LockService extends BaseService {

    private $cache;
    private $validator;
    private $lock_repo;

    const CLIENT_LIST = 'lock_list';
    const CLIENT_SCOPE = 'lock';

    function __construct(CacheManagerInterface $cache, ValidatorInterface $validator, LockRepository $lock_repo)
    {
        $this->cache = $cache;
        $this->validator = $validator;
        $this->lock_repo = $lock_repo;
    }

    /**
     * Gets list of locks
     * @param bool $only_active
     * @return mixed
     */
    public function getLockList($only_active = true){
        return $this->getList(self::LOCK_LIST, self::LOCK_SCOPE, $this->lock_repo, $only_active);
    }

    /**
     * Gets a single lock
     * @param $id
     * @return mixed
     */
    public function getLock($id){
        return $this->get(self::LOCK_LIST, self::LOCK_SCOPE, $this->lock_repo, $id);
    }

    /**
     * Adds a single lock
     * @param $lock
     * @return mixed
     */
    public function addLock($lock){
        $this->add(self::LOCK_LIST, self::LOCK_SCOPE, $this->lock_repo, $lock);
        return $this;
    }

    /**
     * Removes a single lock
     * @param $id
     * @return mixed
     */
    public function removeLock($id){
        $this->remove(self::LOCK_LIST, self::LOCK_SCOPE, $this->lock_repo, $id);
        return $this;
    }

    /**
     * Updates a single country
     * @param $lock
     * @return mixed
     */
    public function updateLock($lock){
        $this->update(self::LOCK_LIST, self::LOCK_SCOPE, $this->lock_repo, $lock);
        return $this;
    }
} 