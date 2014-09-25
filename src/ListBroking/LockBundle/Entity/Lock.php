<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LockBundle\Entity;

use ListBroking\LockBundle\Exception\InvalidLockStatusException;
use ListBroking\LockBundle\Exception\InvalidLockTypeException;

class Lock {

    private $id;

    private $is_active;

    private $status;

    private $type;

    /* ENUMS */
    private $lock_status = array ();
    private $lock_types = array ();

    function __construct()
    {
       $this->lock_status[] = 'LOCK_STATUS_OPEN';
       $this->lock_status[] = 'LOCK_STATUS_CLOSED';
       $this->lock_status[] = 'LOCK_STATUS_EXPIRED';

       $this->lock_types[] = 'LOCK_TYPE_RESERVED';
       $this->lock_types[] = 'LOCK_TYPE_CLIENT';
       $this->lock_types[] = 'LOCK_TYPE_CAMPAIGN';
       $this->lock_types[] = 'LOCK_TYPE_CATEGORY';
       $this->lock_types[] = 'LOCK_TYPE_SUB_CATEGORY';
    }


    /**
     * Saves a future timestamp for the lock expiration time
     * @var
     */
    private $expiration_date;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * @param mixed $is_active
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @throws InvalidLockStatusException
     */
    public function setStatus($status)
    {
        if(!in_array($status, $this->lock_status))
        {
            throw new InvalidLockStatusException('Invalid lock status, must be: ' . print_r(array_values($this->lock_status)));
        }

        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @throws InvalidLockTypeException
     */
    public function setType($type)
    {
        if(!in_array($type, $this->lock_types)){
            throw new InvalidLockTypeException('Invalid lock type, must be: ' . print_r((array_values($this->lock_types))));
        }

        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getExpirationDate()
    {
        return $this->expiration_date;
    }

    /**
     * @param mixed $expiration_date
     */
    public function setExpirationDate($expiration_date)
    {
        $this->expiration_date = $expiration_date;
    }
}