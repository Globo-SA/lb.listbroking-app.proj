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
}
