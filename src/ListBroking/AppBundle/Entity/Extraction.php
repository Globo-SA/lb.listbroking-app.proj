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

use Adclick\DoctrineBehaviorBundle\Behavior\BlameableEntityBehavior;
use Adclick\DoctrineBehaviorBundle\Behavior\TimestampableEntityBehavior;
use Doctrine\Common\Collections\ArrayCollection;

class Extraction {

    const CACHE_ID = 'extraction';

    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    const STATUS_CONFIGURATION = 0;
    const STATUS_FILTRATION = 1;
    const STATUS_CONFIRMATION = 2;
    const STATUS_FINAL = 3;

    protected $id;

    protected $is_active;

    protected $name;

    protected $quantity;

    protected $filters;

    protected $payout;

    protected $status;

    protected $campaign;

    protected $contacts;

    function __construct()
    {
        $this->contacts = new ArrayCollection();
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
    public function getName()
    {
        return $this->name;
    }/**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param mixed $filters
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return mixed
     */
    public function getPayout()
    {
        return $this->payout;
    }

    /**
     * @param mixed $payout
     */
    public function setPayout($payout)
    {
        $this->payout = $payout;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
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
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
     * @return ArrayCollection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact){

        // Only add if its new
        if(!$this->contacts->contains($contact)){
            $contact->addExtraction($this);
            $this->contacts[] = $contact;
        }
    }

    public function removeContact(Contact $contact){
        $this->contacts->removeElement($contact);
    }
}