<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\CoreBundle\Entity;

use Adclick\DoctrineBehaviorBundle\Behavior\BlameableEntityBehavior,
    Adclick\DoctrineBehaviorBundle\Behavior\TimestampableEntityBehavior
    ;
use Doctrine\Common\Collections\ArrayCollection;
use ListBroking\LeadBundle\Entity\Contact;
use ListBroking\LeadBundle\Entity\Lead;
use ListBroking\LeadBundle\Entity\Source;

class Country {
    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    protected $id;

    protected $is_active;

    protected $name;

    protected $iso_code;

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
     * @return mixed
     */
    public function getIsoCode()
    {
        return $this->iso_code;
    }

    /**
     * @param mixed $iso_code
     */
    public function setIsoCode($iso_code)
    {
        $this->iso_code = $iso_code;
    }
}