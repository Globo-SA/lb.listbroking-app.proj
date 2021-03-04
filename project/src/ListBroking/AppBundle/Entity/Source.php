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

class Source
{

    const CACHE_ID = 'source';

    use TimestampableEntityBehavior;

    protected $id;

    /**
     * @var Owner
     */
    protected $owner;

    /**
     * @var Brand
     */
    protected $brand;

    protected $country;

    protected $name;

    protected $external_id;

    protected $contacts;


    function __construct ()
    {
        $this->contacts = new ArrayCollection();
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
    public function getExternalId ()
    {
        return $this->external_id;
    }

    /**
     * @param $external_id
     */
    public function setExternalId ($external_id)
    {
        $this->external_id = $external_id;
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
     * @return Owner
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Owner $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     */
    public function setBrand(Brand $brand): void
    {
        $this->brand = $brand;
    }

    /**
     * @param Contact $contact
     */
    public function addContacts (Contact $contact)
    {
        $contact->setSource($this);
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
     * Add contact
     *
     * @param \ListBroking\AppBundle\Entity\Contact $contact
     *
     * @return Source
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

    /**
     * Get contacts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContacts()
    {
        return $this->contacts;
    }
}
