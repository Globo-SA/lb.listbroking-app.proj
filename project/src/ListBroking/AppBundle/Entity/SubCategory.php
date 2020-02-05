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

class SubCategory
{

    const CACHE_ID = 'sub_category';

    use TimestampableEntityBehavior;

    protected $id;

    protected $name;

    protected $category;

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
    public function getCategory ()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory ($category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getContacts ()
    {
        return $this->contacts;
    }

    /**
     * @param mixed $contacts
     */
    public function setContacts ($contacts)
    {
        $this->contacts = $contacts;
    }

    /**
     * Add contact
     *
     * @param \ListBroking\AppBundle\Entity\Contact $contact
     *
     * @return SubCategory
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
