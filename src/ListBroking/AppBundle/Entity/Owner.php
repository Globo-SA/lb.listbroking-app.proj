<?php
/**
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use ListBroking\AppBundle\Behavior\TimestampableEntityBehavior;

class Owner
{

    const CACHE_ID = 'owner';

    use TimestampableEntityBehavior;

    protected $id;

    protected $name;

    protected $account_name;

    protected $phone;

    protected $email;

    protected $country;

    protected $priority;

    protected $notificationEmailAddress;

    protected $sources;

    protected $contacts;

    function __construct ()
    {
        $this->contacts = new ArrayCollection();
        $this->sources = new ArrayCollection();
    }

    function __toString ()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAccountName ()
    {
        return $this->account_name;
    }

    /**
     * @param mixed $account_name
     */
    public function setAccountName ($account_name)
    {
        $this->account_name = $account_name;
    }

    /**
     * @return mixed
     */
    public function getContacts ()
    {
        return $this->contacts;
    }

    /**
     * @param Contact $contact
     */
    public function addContacts (Contact $contact)
    {
        $contact->setOwner($this);
        $this->contacts[] = $contact;
    }

    /**
     * @param Contact $contact
     */
    public function removeContacts (Contact $contact)
    {
        $this->contacts->removeElement($contact);
    }

    /**
     * @return mixed
     */
    public function getCountry ()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry ($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getEmail ()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail ($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getName ()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName ($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPhone ()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone ($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getPriority ()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority ($priority)
    {
        $this->priority = $priority;
    }

    /**
     * Set notificationEmailAddress
     *
     * @param string $notificationEmailAddress
     *
     * @return Owner
     */
    public function setNotificationEmailAddress($notificationEmailAddress)
    {
        $this->notificationEmailAddress = $notificationEmailAddress;

        return $this;
    }

    /**
     * Get notificationEmailAddress
     *
     * @return string
     */
    public function getNotificationEmailAddress()
    {
        return $this->notificationEmailAddress;
    }

    /**
     * @return mixed
     */
    public function getSources ()
    {
        return $this->sources;
    }

    public function addSources (Source $source)
    {
        $source->setOwner($this);
        $this->sources[] = $source;
    }

    public function removeSources (Source $source)
    {
        $this->sources->removeElement($source);
    }

    /**
     * Add source
     *
     * @param \ListBroking\AppBundle\Entity\Source $source
     *
     * @return Owner
     */
    public function addSource(\ListBroking\AppBundle\Entity\Source $source)
    {
        $this->sources[] = $source;

        return $this;
    }

    /**
     * Remove source
     *
     * @param \ListBroking\AppBundle\Entity\Source $source
     */
    public function removeSource(\ListBroking\AppBundle\Entity\Source $source)
    {
        $this->sources->removeElement($source);
    }

    /**
     * Add contact
     *
     * @param \ListBroking\AppBundle\Entity\Contact $contact
     *
     * @return Owner
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
