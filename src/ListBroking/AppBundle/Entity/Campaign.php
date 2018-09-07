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

class Campaign
{

    const CACHE_ID = 'campaign';

    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    protected $id;

    protected $name;

    protected $description;

    protected $external_id;

    protected $account_name;

    protected $account_id;

    protected $notificationEmailAddress;

    protected $client;

    protected $extractions;

    function __construct()
    {
        $this->extractions = new ArrayCollection();
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
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
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
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExternalId()
    {
        return $this->external_id;
    }

    /**
     * @param mixed $external_id
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
     * @return $this
     */
    public function setAccountName($account_name)
    {
        $this->account_name = $account_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * @param mixed $account_id
     * @return $this
     */
    public function setAccountId($account_id)
    {
        $this->account_id = $account_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotificationEmailAddress()
    {
        return $this->notificationEmailAddress;
    }

    /**
     * @param mixed $notificationEmailAddress
     *
     * @return Campaign
     */
    public function setNotificationEmailAddress($notificationEmailAddress)
    {
        $this->notificationEmailAddress = $notificationEmailAddress;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @param Extraction $extraction
     * @return $this;
     */
    public function addExtraction(Extraction $extraction){
    	$extraction->setCampaign($this);
        $this->extractions[] = $extraction;
        return $this;
    }

    /**
     * @param Extraction $extraction
     * @return $this
     */
    public function removeExtraction(Extraction $extraction){
        $this->extractions->removeElement($extraction);
        return $this;
    }
}
