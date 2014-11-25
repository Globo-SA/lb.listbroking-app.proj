<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Entity;

use ListBroking\AppBundle\Behavior\BlameableEntityBehavior;
use ListBroking\AppBundle\Behavior\TimestampableEntityBehavior;
use Doctrine\Common\Collections\ArrayCollection;

class Lead {

    const CACHE_ID = 'lead';

    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    protected $id;

    protected $phone;

    protected $is_mobile;

    protected $in_opposition;

    protected $country;

    protected $contacts;

    protected $locks;

    function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->locks = new ArrayCollection();
    }

    function __toString()
    {
        return $this->id;
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
    public function getInOpposition()
    {
        return $this->in_opposition;
    }

    /**
     * @param mixed $in_opposition
     */
    public function setInOpposition($in_opposition)
    {
        $this->in_opposition = $in_opposition;
    }

    /**
     * @return mixed
     */
    public function getIsMobile()
    {
        return $this->is_mobile;
    }

    /**
     * @param mixed $is_mobile
     */
    public function setIsMobile($is_mobile)
    {
        $this->is_mobile = $is_mobile;
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
     * @return mixed
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param Contact $contact
     */
    public function addContacts(Contact $contact){
        $contact->setLead($this);
        $this->contacts[] = $contact;
    }

    /**
     * @param Contact $contact
     */
    public function removeContacts(Contact $contact){
        $this->contacts->removeElement($contact);
    }

    /**
     * @param Lock $lock
     */
    public function addLocks(Lock $lock){
    	$lock->setLead($this);
        $this->locks[] = $lock;
    }

    /**
     * @param Lock $lock
     */
    public function removeLocks(Lock $lock){
        $this->locks->removeElement($lock);
    }
} 