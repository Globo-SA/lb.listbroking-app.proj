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

use ListBroking\AppBundle\Behavior\BlameableEntityBehavior,
    ListBroking\AppBundle\Behavior\TimestampableEntityBehavior
    ;

class Country {

    const CACHE_ID = 'country';

    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    protected $id;



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