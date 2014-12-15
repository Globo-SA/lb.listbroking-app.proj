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

use ListBroking\AppBundle\Behavior\TimestampableEntityBehavior;

/**
 * This Entity must not be used in any report or
 * prod extraction, this is just a staging table
 * of ETL processes
 * Class StagingContact
 * @package ListBroking\AppBundle\Entity
 */
class StagingContact {

    use TimestampableEntityBehavior;

    protected $id;

    protected $processed = 0;

    protected $valid = 0;

    /**
     * Contact Information
     */
    protected $phone;

    protected $is_mobile;

    protected $in_opposition;

    protected $email;

    protected $firstname;

    protected $lastname;

    protected $birthdate;

    protected $address;

    protected $postalcode1;

    protected $postalcode2;

    protected $ipaddress;

    protected $gender;

    protected $district;

    protected $county;

    protected $parish;

    protected $country;

    protected $validations;


    /**
     * Owner Information
     */
    protected $owner;

    protected $source_name;

    protected $source_external_id;

    protected $source_country;


    /**
     * Category Information
     */
    protected $sub_category;


    /**
     * All information saved as json_array
     */
    protected $post_request;


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
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * @param mixed $processed
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;
    }

    /**
     * @return mixed
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * @param mixed $valid
     */
    public function setValid($valid)
    {
        $this->valid = $valid;
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
    public function getInOpposition()
    {
        return $this->in_opposition;
    }

    /**
     * @param mixed $in_opposition
     */
    public function setInOpposition($in_opposition)
    {
        $this->in_opposition = $in_opposition;
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
    public function getIsMobile()
    {
        return $this->is_mobile;
    }

    /**
     * @param mixed $is_mobile
     */
    public function setIsMobile($is_mobile)
    {
        $this->is_mobile = $is_mobile;
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
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getPostRequest()
    {
        return $this->post_request;
    }

    /**
     * @param mixed $post_request
     */
    public function setPostRequest($post_request)
    {
        $this->post_request = $post_request;
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
    public function getSourceCountry()
    {
        return $this->source_country;
    }

    /**
     * @param mixed $source_country
     */
    public function setSourceCountry($source_country)
    {
        $this->source_country = $source_country;
    }

    /**
     * @return mixed
     */
    public function getSourceExternalId()
    {
        return $this->source_external_id;
    }

    /**
     * @param mixed $source_external_id
     */
    public function setSourceExternalId($source_external_id)
    {
        $this->source_external_id = $source_external_id;
    }

    /**
     * @return mixed
     */
    public function getSourceName()
    {
        return $this->source_name;
    }

    /**
     * @param mixed $source_name
     */
    public function setSourceName($source_name)
    {
        $this->source_name = $source_name;
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
    public function getValidations()
    {
        return $this->validations;
    }

    /**
     * @param mixed $validations
     */
    public function setValidations($validations)
    {
        $this->validations = $validations;
    }

}