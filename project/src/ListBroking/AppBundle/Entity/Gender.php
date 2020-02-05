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

class Gender
{
    use TimestampableEntityBehavior;

    const CACHE_ID    = 'gender';

    const EMPTY_FIELD = 'N/A';

    const MALE        = 'M';

    const FEMALE      = 'F';

    protected $id;

    protected $name;

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
    public function getContacts ()
    {
        return $this->contacts;
    }

    /**
     * @param Contact $contact
     */
    public function addContacts (Contact $contact)
    {
        $contact->setGender($this);
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
     * @return Gender
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
