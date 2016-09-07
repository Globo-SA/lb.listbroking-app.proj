<?php
/**
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Entity;

use ListBroking\AppBundle\Behavior\TimestampableEntityBehavior;

/**
 * Function: Stores all non-processed contacts
 *
 * This Entity must not be used in any report or
 * prod extraction, this is just a staging table
 * of ETL processes
 * @package ListBroking\AppBundle\Entity
 */
class StagingContact
{

    const IMPORT_TEMPLATE_FILE_EXTENSION = 'csv';

    const IMPORT_TEMPLATE_FILENAME       = 'staging_contact_import_template';

    public static $import_template = array(
        'external_id'         => '',
        'contact_id'          => '',
        'phone'               => '',
        'email'               => '',
        'firstname'           => '',
        'lastname'            => '',
        'birthdate'           => '',
        'address'             => '',
        'postalcode1'         => '',
        'postalcode2'         => '',
        'ipaddress'           => '',
        'gender'              => '',
        'district'            => '',
        'county'              => '',
        'parish'              => '',
        'country'             => '',
        'source_name'         => '',
        'source_external_id'  => '',
        'source_country'      => '',
        'sub_category'        => '',
        'date'                => '',
        'initial_lock_expiration_date' => '',
        'post_request'        => ''
    );

    use TimestampableEntityBehavior;

    protected $id;

    protected $external_id;

    protected $processed = 0;

    protected $valid     = 0;

    protected $running   = 0;

    protected $update    = 0;

    /**
     * Contact Information
     */
    protected $phone;

    protected $is_mobile     = 0;

    protected $in_opposition = 0;

    protected $lead_id;

    protected $contact_id;

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

    protected $owner;

    protected $source_name;

    protected $source_external_id;

    protected $source_country;

    protected $sub_category;

    protected $date;

    protected $initial_lock_expiration_date;

    protected $post_request;

    protected $validations;

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
    public function getExternalId ()
    {
        return $this->external_id;
    }

    /**
     * @param mixed $external_id
     */
    public function setExternalId ($external_id)
    {
        $this->external_id = $external_id;
    }

    /**
     * @return mixed
     */
    public function getProcessed ()
    {
        return $this->processed;
    }

    /**
     * @param mixed $processed
     */
    public function setProcessed ($processed)
    {
        $this->processed = $processed;
    }

    /**
     * @return mixed
     */
    public function getValid ()
    {
        return $this->valid;
    }

    /**
     * @param mixed $valid
     */
    public function setValid ($valid)
    {
        $this->valid = $valid;
    }

    /**
     * @return int
     */
    public function getRunning ()
    {
        return $this->running;
    }

    /**
     * @param int $running
     */
    public function setRunning ($running)
    {
        $this->running = $running;
    }

    /**
     * @return int
     */
    public function getUpdate ()
    {
        return $this->update;
    }

    /**
     * @param int $update
     */
    public function setUpdate ($update)
    {
        $this->update = $update;
    }

    /**
     * @return mixed
     */
    public function getAddress ()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress ($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getBirthdate ()
    {
        return $this->birthdate;
    }

    /**
     * @param mixed $birthdate
     */
    public function setBirthdate ($birthdate)
    {
        $this->birthdate = $birthdate;
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
    public function getCounty ()
    {
        return $this->county;
    }

    /**
     * @param mixed $county
     */
    public function setCounty ($county)
    {
        $this->county = $county;
    }

    /**
     * @return mixed
     */
    public function getDistrict ()
    {
        return $this->district;
    }

    /**
     * @param mixed $district
     */
    public function setDistrict ($district)
    {
        $this->district = $district;
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
    public function getFirstname ()
    {
        return $this->firstname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstname ($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getGender ()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender ($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getInOpposition ()
    {
        return $this->in_opposition;
    }

    /**
     * @param mixed $in_opposition
     */
    public function setInOpposition ($in_opposition)
    {
        $this->in_opposition = $in_opposition;
    }

    /**
     * @return mixed
     */
    public function getContactId ()
    {
        return $this->contact_id;
    }

    /**
     * @param mixed $contact_id
     */
    public function setContactId ($contact_id)
    {
        $this->contact_id = $contact_id;
    }

    /**
     * @return mixed
     */
    public function getLeadId ()
    {
        return $this->lead_id;
    }

    /**
     * @param mixed $lead_id
     */
    public function setLeadId ($lead_id)
    {
        $this->lead_id = $lead_id;
    }

    /**
     * @return mixed
     */
    public function getIpaddress ()
    {
        return $this->ipaddress;
    }

    /**
     * @param mixed $ipaddress
     */
    public function setIpaddress ($ipaddress)
    {
        $this->ipaddress = $ipaddress;
    }

    /**
     * @return mixed
     */
    public function getIsMobile ()
    {
        return $this->is_mobile;
    }

    /**
     * @param mixed $is_mobile
     */
    public function setIsMobile ($is_mobile)
    {
        $this->is_mobile = $is_mobile;
    }

    /**
     * @return mixed
     */
    public function getLastname ()
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastname ($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return mixed
     */
    public function getOwner ()
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     */
    public function setOwner ($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return mixed
     */
    public function getParish ()
    {
        return $this->parish;
    }

    /**
     * @param mixed $parish
     */
    public function setParish ($parish)
    {
        $this->parish = $parish;
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
    public function getPostRequest ()
    {
        return $this->post_request;
    }

    /**
     * @param mixed $post_request
     */
    public function setPostRequest ($post_request)
    {
        $this->post_request = $post_request;
    }

    /**
     * @return mixed
     */
    public function getPostalcode1 ()
    {
        return $this->postalcode1;
    }

    /**
     * @param mixed $postalcode1
     */
    public function setPostalcode1 ($postalcode1)
    {
        $this->postalcode1 = $postalcode1;
    }

    /**
     * @return mixed
     */
    public function getPostalcode2 ()
    {
        return $this->postalcode2;
    }

    /**
     * @param mixed $postalcode2
     */
    public function setPostalcode2 ($postalcode2)
    {
        $this->postalcode2 = $postalcode2;
    }

    /**
     * @return mixed
     */
    public function getSourceCountry ()
    {
        return $this->source_country;
    }

    /**
     * @param mixed $source_country
     */
    public function setSourceCountry ($source_country)
    {
        $this->source_country = $source_country;
    }

    /**
     * @return mixed
     */
    public function getSourceExternalId ()
    {
        return $this->source_external_id;
    }

    /**
     * @param mixed $source_external_id
     */
    public function setSourceExternalId ($source_external_id)
    {
        $this->source_external_id = $source_external_id;
    }

    /**
     * @return mixed
     */
    public function getSourceName ()
    {
        return $this->source_name;
    }

    /**
     * @param mixed $source_name
     */
    public function setSourceName ($source_name)
    {
        $this->source_name = $source_name;
    }

    /**
     * @return mixed
     */
    public function getSubCategory ()
    {
        return $this->sub_category;
    }

    /**
     * @param mixed $sub_category
     */
    public function setSubCategory ($sub_category)
    {
        $this->sub_category = $sub_category;
    }

    /**
     * @return mixed
     */
    public function getDate ()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate ($date)
    {
        if ( ! is_object($date) )
        {
            $date = new \DateTime($date);
        }
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getInitialLockExpirationDate ()
    {
        return $this->initial_lock_expiration_date;
    }

    /**
     * @param mixed $initial_lock_expiration_date
     */
    public function setInitialLockExpirationDate ($initial_lock_expiration_date)
    {
        $this->initial_lock_expiration_date = $initial_lock_expiration_date;
    }

    /**
     * @return mixed
     */
    public function getValidations ()
    {
        return $this->validations;
    }

    /**
     * @param mixed $validations
     */
    public function setValidations ($validations)
    {
        $this->validations = $validations;
    }
}