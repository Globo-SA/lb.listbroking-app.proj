<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LeadBundle\Entity;


use Adclick\DoctrineBehaviorBundle\Behavior\BlameableEntityBehavior;
use Adclick\DoctrineBehaviorBundle\Behavior\TimestampableEntityBehavior;

class Contact {
    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    protected $id;

    protected $email;

    protected $firstname;

    protected $lastname;

    protected $birthdate;

    protected $address;

    protected $postalcode1;

    protected $postalcode2;

    protected $ipaddress;

    protected $lead;

    protected $source;

    protected $owner;

    protected $sub_category;

    protected $gender;

    protected $district;

    protected $county;

    protected $parish;

    protected $country;

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
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * @param mixed $birthdate
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    }

    /**
     * @return mixed
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * @param mixed $county
     */
    public function setCounty($county)
    {
        $this->county = $county;
    }

    /**
     * @return mixed
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param mixed $district
     */
    public function setDistrict($district)
    {
        $this->district = $district;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getIpaddress()
    {
        return $this->ipaddress;
    }

    /**
     * @param mixed $ipaddress
     */
    public function setIpaddress($ipaddress)
    {
        $this->ipaddress = $ipaddress;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return mixed
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param mixed $lead
     */
    public function setLead($lead)
    {
        $this->lead = $lead;
    }

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return mixed
     */
    public function getParish()
    {
        return $this->parish;
    }

    /**
     * @param mixed $parish
     */
    public function setParish($parish)
    {
        $this->parish = $parish;
    }

    /**
     * @return mixed
     */
    public function getPostalcode1()
    {
        return $this->postalcode1;
    }

    /**
     * @param mixed $postalcode1
     */
    public function setPostalcode1($postalcode1)
    {
        $this->postalcode1 = $postalcode1;
    }

    /**
     * @return mixed
     */
    public function getPostalcode2()
    {
        return $this->postalcode2;
    }

    /**
     * @param mixed $postalcode2
     */
    public function setPostalcode2($postalcode2)
    {
        $this->postalcode2 = $postalcode2;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSubCategory()
    {
        return $this->sub_category;
    }

    /**
     * @param mixed $sub_category
     */
    public function setSubCategory($sub_category)
    {
        $this->sub_category = $sub_category;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }
}