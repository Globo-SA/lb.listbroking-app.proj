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

use ListBroking\LockBundle\Engine\LockEngine;
use ListBroking\LockBundle\Exception\InvalidLockStatusException;
use ListBroking\LockBundle\Exception\InvalidLockTypeException;

use Adclick\DoctrineBehaviorBundle\Behavior\BlameableEntityBehavior,
    Adclick\DoctrineBehaviorBundle\Behavior\TimestampableEntityBehavior
    ;

class Lock {

    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    private $id;

    private $is_active;

    private $status;

    private $type;

    private $lead;

    private $client;

    private $campaign;

    private $category;

    private $sub_category;

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
        if(!in_array($status, array_keys(LockEngine::lockStatus())))
        {
            throw new InvalidLockStatusException('Invalid lock status, must be: ' . print_r(LockEngine::lockStatus()));
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
        if(!in_array($type, array_keys(LockEngine::lockTypes()))){
            throw new InvalidLockTypeException('Invalid lock type, must be: ' . print_r(LockEngine::lockTypes()));
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

    /**
     * @return mixed
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param mixed $lead
     */
    public function setLead($lead)
    {
        $this->lead = $lead;
    }

    /**
     * @return mixed
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param mixed $campaign
     */
    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return mixed
     */
    public function getSubCategory()
    {
        return $this->sub_category;
    }

    /**
     * @param mixed $sub_category
     */
    public function setSubCategory($sub_category)
    {
        $this->sub_category = $sub_category;
    }

}