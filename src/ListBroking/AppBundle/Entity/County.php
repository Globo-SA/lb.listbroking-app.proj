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

class County {

    const CACHE_ID = 'county';

    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    protected $id;    protected $name;

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
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param Contact $contact
     */
    public function addContacts(Contact $contact){
        $contact->setCounty($this);
        $this->contacts[] = $contact;
    }

    /**
     * @param Contact $contact
     */
    public function removeContacts(Contact $contact){
        $this->contacts->removeElement($contact);
    }
}