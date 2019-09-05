<?php

namespace ListBroking\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * LeadHist
 */
class LeadHist
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var boolean
     */
    private $is_mobile = 0;

    /**
     * @var boolean
     */
    private $in_opposition = 0;

    /**
     * @var boolean
     */
    private $is_ready_to_use;

    /**
     * @var boolean
     */
    private $is_sms_ok = 0;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $contacts;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $locks;

    /**
     * @var Country
     */
    private $country;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->locks = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return LeadHist
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set isMobile
     *
     * @param boolean $isMobile
     *
     * @return LeadHist
     */
    public function setIsMobile($isMobile)
    {
        $this->is_mobile = $isMobile;

        return $this;
    }

    /**
     * Get isMobile
     *
     * @return boolean
     */
    public function getIsMobile()
    {
        return $this->is_mobile;
    }

    /**
     * Set inOpposition
     *
     * @param boolean $inOpposition
     *
     * @return LeadHist
     */
    public function setInOpposition($inOpposition)
    {
        $this->in_opposition = $inOpposition;

        return $this;
    }

    /**
     * Get inOpposition
     *
     * @return boolean
     */
    public function getInOpposition()
    {
        return $this->in_opposition;
    }

    /**
     * Set isReadyToUse
     *
     * @param boolean $isReadyToUse
     *
     * @return LeadHist
     */
    public function setIsReadyToUse($isReadyToUse)
    {
        $this->is_ready_to_use = $isReadyToUse;

        return $this;
    }

    /**
     * Get isReadyToUse
     *
     * @return boolean
     */
    public function getIsReadyToUse()
    {
        return $this->is_ready_to_use;
    }

    /**
     * Set isSmsOk
     *
     * @param boolean $isSmsOk
     *
     * @return LeadHist
     */
    public function setIsSmsOk($isSmsOk)
    {
        $this->is_sms_ok = $isSmsOk;

        return $this;
    }

    /**
     * Get isSmsOk
     *
     * @return boolean
     */
    public function getIsSmsOk()
    {
        return $this->is_sms_ok;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return LeadHist
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return LeadHist
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Add contact
     *
     * @param ContactHist $contact
     *
     * @return LeadHist
     */
    public function addContact(ContactHist $contact)
    {
        $this->contacts[] = $contact;

        return $this;
    }

    /**
     * Remove contact
     *
     * @param ContactHist $contact
     */
    public function removeContact(ContactHist $contact)
    {
        $this->contacts->removeElement($contact);
    }

    /**
     * Get contacts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Add lock
     *
     * @param Lock $lock
     *
     * @return LeadHist
     */
    public function addLock(Lock $lock)
    {
        $this->locks[] = $lock;

        return $this;
    }

    /**
     * Remove lock
     *
     * @param Lock $lock
     */
    public function removeLock(Lock $lock)
    {
        $this->locks->removeElement($lock);
    }

    /**
     * Get locks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLocks()
    {
        return $this->locks;
    }

    /**
     * Set country
     *
     * @param Country $country
     *
     * @return LeadHist
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }
}

