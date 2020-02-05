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

use ListBroking\AppBundle\Behavior\TimestampableEntityBehavior;
use Doctrine\Common\Collections\ArrayCollection;

class Lead {

    const CACHE_ID  = 'lead';
    const PHONE_KEY = 'phone';

    use TimestampableEntityBehavior;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $phone;

    /**
     * @var boolean
     */
    protected $is_mobile;

    /**
     * @var boolean
     */
    protected $is_sms_ok = false;

    /**
     * @var boolean
     */
    protected $in_opposition;

    /**
     * @var boolean
     */
    protected $is_ready_to_use = false;

    /**
     * @var int
     */
    protected $date;

    /**
     * @var string
     */
    protected $country;

    /**
     * @var Contact[]
     */
    protected $contacts;

    /**
     * @var ArrayCollection
     */
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return boolean
     */
    public function getInOpposition()
    {
        return $this->in_opposition;
    }

    /**
     * @param boolean $in_opposition
     */
    public function setInOpposition($in_opposition)
    {
        $this->in_opposition = $in_opposition;
    }

    /**
     * @return boolean
     */
    public function getIsMobile()
    {
        return $this->is_mobile;
    }

    /**
     * @return boolean
     */
    public function getIsSmsOk()
    {
        return $this->is_sms_ok;
    }

    /**
     * @return boolean
     */
    public function getIsReadyToUse ()
    {
        return $this->is_ready_to_use;
    }

    /**
     * @param boolean $is_ready_to_use
     */
    public function setIsReadyToUse ($is_ready_to_use)
    {
        $this->is_ready_to_use = $is_ready_to_use;
    }

    /**
     * @param boolean $is_mobile
     */
    public function setIsMobile($is_mobile)
    {
        $this->is_mobile = $is_mobile;
    }

    /**
     * @param boolean $is_sms_ok
     */
    public function setIsSmsOk($is_sms_ok)
    {
        $this->is_sms_ok = $is_sms_ok;
    }

    /**
     * @return int
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return Contact[]
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
     * @return ArrayCollection
     */
    public function getLocks()
    {
        return $this->locks;
    }

    /**
     * @param Lock $lock
     */
    public function addLock(Lock $lock){
    	$lock->setLead($this);
        $this->locks[] = $lock;
    }

    /**
     * @param Lock $lock
     */
    public function removeLock(Lock $lock){
        $this->locks->removeElement($lock);
    }

    /**
     * Add contact
     *
     * @param \ListBroking\AppBundle\Entity\Contact $contact
     *
     * @return Lead
     */
    public function addContact(\ListBroking\AppBundle\Entity\Contact $contact)
    {
        $this->contacts[] = $contact;

        return $this;
    }

    /**
     * Remove contact
     *
     * @param \ListBroking\AppBundle\Entity\Contact $contact
     */
    public function removeContact(\ListBroking\AppBundle\Entity\Contact $contact)
    {
        $this->contacts->removeElement($contact);
    }
}
