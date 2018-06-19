<?php

/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Entity;

use ListBroking\AppBundle\Behavior\BlameableEntityBehavior,
    ListBroking\AppBundle\Behavior\TimestampableEntityBehavior
    ;

use Doctrine\Common\Collections\ArrayCollection;

class Client
{
    const CACHE_ID   = 'client';
    const NOTIFY_KEY = 'notify';
    const NOTIFY_YES = 'yes';
    const NOTIFY_NO  = 'no';

    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    protected $id;

    protected $external_id;

    protected $name;

    protected $account_name;

    protected $phone;

    protected $email_address;

    protected $campaigns;

    function __construct()
    {
        $this->campaigns = new ArrayCollection();
    }

    function __toString()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getExternalId()
    {
        return $this->external_id;
    }

    /**
     * @param int $external_id
     * @return $this
     */
    public function setExternalId($external_id)
    {
        $this->external_id = $external_id;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getAccountName()
    {
        return $this->account_name;
    }

    /**
     * @param mixed $account_name
     */
    public function setAccountName($account_name)
    {
        $this->account_name = $account_name;
    }

    /**
     * @return mixed
     */
    public function getEmailAddress()
    {
        return $this->email_address;
    }

    /**
     * @param mixed $email
     */
    public function setEmailAddress($email)
    {
        $this->email_address = $email;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @param Campaign $campaign
     */
    public function addCampaign(Campaign $campaign){
        $campaign->setClient($this);
        $this->campaigns[] = $campaign;
    }

    /**
     * @param Campaign $campaign
     */
    public function removeCampaign(Campaign $campaign){
        $this->campaigns->removeElement($campaign);
    }

    /**
     * @return ArrayCollection
     */
    public function getCampaigns()
    {
        return $this->campaigns;
    }



} 