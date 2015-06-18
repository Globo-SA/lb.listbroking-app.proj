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
use ListBroking\AppBundle\Entity\ExtractionContact;

class Contact {
    use TimestampableEntityBehavior,
        BlameableEntityBehavior;


    function __construct()
    {
        $this->extractions = new ArrayCollection();
    }

    function __toString()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    // GENERATED STUFF

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $external_id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var \DateTime
     */
    private $birthdate;

    /**
     * @var string
     */
    private $address;

    /**
     * @var integer
     */
    private $postalcode1;

    /**
     * @var integer
     */
    private $postalcode2;

    /**
     * @var string
     */
    private $ipaddress;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var array
     */
    private $post_request;

    /**
     * @var array
     */
    private $validations;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $extraction_contacts;

    /**
     * @var \ListBroking\AppBundle\Entity\Lead
     */
    private $lead;

    /**
     * @var \ListBroking\AppBundle\Entity\Source
     */
    private $source;

    /**
     * @var \ListBroking\AppBundle\Entity\Owner
     */
    private $owner;

    /**
     * @var \ListBroking\AppBundle\Entity\SubCategory
     */
    private $sub_category;

    /**
     * @var \ListBroking\AppBundle\Entity\Gender
     */
    private $gender;

    /**
     * @var \ListBroking\AppBundle\Entity\District
     */
    private $district;

    /**
     * @var \ListBroking\AppBundle\Entity\County
     */
    private $county;

    /**
     * @var \ListBroking\AppBundle\Entity\Parish
     */
    private $parish;

    /**
     * @var \ListBroking\AppBundle\Entity\Country
     */
    private $country;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set externalId
     *
     * @param string $externalId
     *
     * @return Contact
     */
    public function setExternalId($externalId)
    {
        $this->external_id = $externalId;

        return $this;
    }

    /**
     * Get externalId
     *
     * @return string
     */
    public function getExternalId()
    {
        return $this->external_id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Contact
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return Contact
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return Contact
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set birthdate
     *
     * @param \DateTime $birthdate
     *
     * @return Contact
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get birthdate
     *
     * @return \DateTime
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Contact
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set postalcode1
     *
     * @param integer $postalcode1
     *
     * @return Contact
     */
    public function setPostalcode1($postalcode1)
    {
        $this->postalcode1 = $postalcode1;

        return $this;
    }

    /**
     * Get postalcode1
     *
     * @return integer
     */
    public function getPostalcode1()
    {
        return $this->postalcode1;
    }

    /**
     * Set postalcode2
     *
     * @param integer $postalcode2
     *
     * @return Contact
     */
    public function setPostalcode2($postalcode2)
    {
        $this->postalcode2 = $postalcode2;

        return $this;
    }

    /**
     * Get postalcode2
     *
     * @return integer
     */
    public function getPostalcode2()
    {
        return $this->postalcode2;
    }

    /**
     * Set ipaddress
     *
     * @param string $ipaddress
     *
     * @return Contact
     */
    public function setIpaddress($ipaddress)
    {
        $this->ipaddress = $ipaddress;

        return $this;
    }

    /**
     * Get ipaddress
     *
     * @return string
     */
    public function getIpaddress()
    {
        return $this->ipaddress;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Contact
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set postRequest
     *
     * @param array $postRequest
     *
     * @return Contact
     */
    public function setPostRequest($postRequest)
    {
        $this->post_request = $postRequest;

        return $this;
    }

    /**
     * Get postRequest
     *
     * @return array
     */
    public function getPostRequest()
    {
        return $this->post_request;
    }

    /**
     * Set validations
     *
     * @param array $validations
     *
     * @return Contact
     */
    public function setValidations($validations)
    {
        $this->validations = $validations;

        return $this;
    }

    /**
     * Get validations
     *
     * @return array
     */
    public function getValidations()
    {
        return $this->validations;
    }

    /**
     * Add extractionContact
     *
     * @param ExtractionContact $extractionContact
     *
     * @return Contact
     */
    public function addExtractionContact(ExtractionContact $extractionContact)
    {
        $this->extraction_contacts[] = $extractionContact;

        return $this;
    }

    /**
     * Remove extractionContact
     *
     * @param ExtractionContact $extractionContact
     */
    public function removeExtractionContact(ExtractionContact $extractionContact)
    {
        $this->extraction_contacts->removeElement($extractionContact);
    }

    /**
     * Get extractionContacts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExtractionContacts()
    {
        return $this->extraction_contacts;
    }

    /**
     * Set lead
     *
     * @param \ListBroking\AppBundle\Entity\Lead $lead
     *
     * @return Contact
     */
    public function setLead(\ListBroking\AppBundle\Entity\Lead $lead)
    {
        $this->lead = $lead;

        return $this;
    }

    /**
     * Get lead
     *
     * @return \ListBroking\AppBundle\Entity\Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * Set source
     *
     * @param \ListBroking\AppBundle\Entity\Source $source
     *
     * @return Contact
     */
    public function setSource(\ListBroking\AppBundle\Entity\Source $source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return \ListBroking\AppBundle\Entity\Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set owner
     *
     * @param \ListBroking\AppBundle\Entity\Owner $owner
     *
     * @return Contact
     */
    public function setOwner(\ListBroking\AppBundle\Entity\Owner $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \ListBroking\AppBundle\Entity\Owner
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set subCategory
     *
     * @param \ListBroking\AppBundle\Entity\SubCategory $subCategory
     *
     * @return Contact
     */
    public function setSubCategory(\ListBroking\AppBundle\Entity\SubCategory $subCategory)
    {
        $this->sub_category = $subCategory;

        return $this;
    }

    /**
     * Get subCategory
     *
     * @return \ListBroking\AppBundle\Entity\SubCategory
     */
    public function getSubCategory()
    {
        return $this->sub_category;
    }

    /**
     * Set gender
     *
     * @param \ListBroking\AppBundle\Entity\Gender $gender
     *
     * @return Contact
     */
    public function setGender(\ListBroking\AppBundle\Entity\Gender $gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return \ListBroking\AppBundle\Entity\Gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set district
     *
     * @param \ListBroking\AppBundle\Entity\District $district
     *
     * @return Contact
     */
    public function setDistrict(\ListBroking\AppBundle\Entity\District $district = null)
    {
        $this->district = $district;

        return $this;
    }

    /**
     * Get district
     *
     * @return \ListBroking\AppBundle\Entity\District
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Set county
     *
     * @param \ListBroking\AppBundle\Entity\County $county
     *
     * @return Contact
     */
    public function setCounty(\ListBroking\AppBundle\Entity\County $county = null)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * Get county
     *
     * @return \ListBroking\AppBundle\Entity\County
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Set parish
     *
     * @param \ListBroking\AppBundle\Entity\Parish $parish
     *
     * @return Contact
     */
    public function setParish(\ListBroking\AppBundle\Entity\Parish $parish = null)
    {
        $this->parish = $parish;

        return $this;
    }

    /**
     * Get parish
     *
     * @return \ListBroking\AppBundle\Entity\Parish
     */
    public function getParish()
    {
        return $this->parish;
    }

    /**
     * Set country
     *
     * @param \ListBroking\AppBundle\Entity\Country $country
     *
     * @return Contact
     */
    public function setCountry(\ListBroking\AppBundle\Entity\Country $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \ListBroking\AppBundle\Entity\Country
     */
    public function getCountry()
    {
        return $this->country;
    }
}
