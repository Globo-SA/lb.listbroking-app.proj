<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LeadBundle\Entity;


use Adclick\DoctrineBehaviorBundle\Behavior\BlameableEntityBehavior;
use Adclick\DoctrineBehaviorBundle\Behavior\TimestampableEntityBehavior;
use Doctrine\Common\Collections\ArrayCollection;

class Source {
    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    protected $id;

    protected $owner;

    protected $country;

    protected $name;

    protected $lc_source_page_id;

    protected $is_active;

    protected $contacts;

    function __construct()
    {
        $this->contacts = new ArrayCollection();
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
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getLcSourcePageId()
    {
        return $this->lc_source_page_id;
    }

    /**
     * @param mixed $lc_source_page_id
     */
    public function setLcSourcePageId($lc_source_page_id)
    {
        $this->lc_source_page_id = $lc_source_page_id;
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
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
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
     * @param Contact $contact
     */
    public function addContacts(Contact $contact){
        $contact->setSource($this);
        $this->contacts[] = $contact;
    }

    /**
     * @param Contact $contact
     */
    public function removeContacts(Contact $contact){
        $this->contacts->removeElement($contact);
    }
} 