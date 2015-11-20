<?php
/**
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\Inflector;
use ListBroking\AppBundle\Behavior\TimestampableEntityBehavior;

class Contact
{
    use TimestampableEntityBehavior;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $is_clean = 0;

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
    private $birthdate = null;

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
     * @var Lead
     */
    private $lead;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var Owner
     */
    private $owner;

    /**
     * @var SubCategory
     */
    private $sub_category;

    /**
     * @var Gender
     */
    private $gender;

    /**
     * @var District
     */
    private $district;

    /**
     * @var County
     */
    private $county;

    /**
     * @var Parish
     */
    private $parish;

    /**
     * @var Country
     */
    private $country;

    function __construct ()
    {
        $this->extractions = new ArrayCollection();
    }

    function __toString ()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Updates the dimensions of the Contact
     *
     * @param $dimensions
     */
    public function updateContactDimensions ($dimensions)
    {
        foreach ( $dimensions as $dimension )
        {
            if ( empty($dimension) )
            {
                continue;
            }
            $reflect = new \ReflectionClass($dimension);
            $set_method = 'set' . $reflect->getShortName();
            $this->$set_method($dimension);
        }
    }

    /**
     * Updates the Facts of the contact using a StagingContact
     *
     * @param StagingContact $s_contact
     */
    public function updateContactFacts (StagingContact $s_contact)
    {
        $birthdate = trim($s_contact->getBirthdate());
        $fields = array(
            'email'           => $s_contact->getEmail(),
            'external_id'     => $s_contact->getExternalId(),
            'firstname'       => $s_contact->getFirstname(),
            'lastname'        => $s_contact->getLastname(),
            'birthdate'       => empty($birthdate) ? null : new \DateTime($s_contact->getBirthdate()),
            'address'         => $s_contact->getAddress(),
            'postalcode1'     => $s_contact->getPostalcode1(),
            'postalcode2'     => $s_contact->getPostalcode2(),
            'ipaddress'       => $s_contact->getIpaddress(),
            'date'            => $s_contact->getDate(),
            'post_request'    => $s_contact->getPostRequest(),

        );

        // If there's a new postalcode1 value,
        // reset the old values before adding the new ones
        if ( ! empty($fields['postalcode1']) )
        {
            $this->postalcode1 = null;
            $this->postalcode2 = null;
        }
        else
        {
            // If there isn't a postalcode1, postalcode2
            // doesn't make sense
            $fields['postalcode2'] = null;
        }

        foreach ( $fields as $field => $new_value )
        {
            $inflector = new Inflector();
            $setMethod = 'set' . $inflector->classify($field);

            if ( empty($new_value) )
            {
                continue;
            }

            $this->$setMethod($new_value);
        }
    }

    /**
     * Get id
     * @return integer
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function isClean ()
    {
        return $this->is_clean;
    }

    /**
     * @param boolean $is_clean
     */
    public function setIsClean ($is_clean)
    {
        $this->is_clean = $is_clean;
    }

    /**
     * Get externalId
     * @return string
     */
    public function getExternalId ()
    {
        return $this->external_id;
    }

    /**
     * Set externalId
     *
     * @param string $externalId
     *
     * @return Contact
     */
    public function setExternalId ($externalId)
    {
        $this->external_id = $externalId;

        return $this;
    }

    /**
     * Get email
     * @return string
     */
    public function getEmail ()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Contact
     */
    public function setEmail ($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get firstname
     * @return string
     */
    public function getFirstname ()
    {
        return $this->firstname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     *
     * @return Contact
     */
    public function setFirstname ($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get lastname
     * @return string
     */
    public function getLastname ()
    {
        return $this->lastname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return Contact
     */
    public function setLastname ($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get birthdate
     * @return \DateTime
     */
    public function getBirthdate ()
    {
        return $this->birthdate;
    }

    /**
     * Set birthdate
     *
     * @param \DateTime $birthdate
     *
     * @return Contact
     */
    public function setBirthdate ($birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get address
     * @return string
     */
    public function getAddress ()
    {
        return $this->address;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Contact
     */
    public function setAddress ($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get postalcode1
     * @return integer
     */
    public function getPostalcode1 ()
    {
        return $this->postalcode1;
    }

    /**
     * Set postalcode1
     *
     * @param integer $postalcode1
     *
     * @return Contact
     */
    public function setPostalcode1 ($postalcode1)
    {
        $this->postalcode1 = $postalcode1;

        return $this;
    }

    /**
     * Get postalcode2
     * @return integer
     */
    public function getPostalcode2 ()
    {
        return $this->postalcode2;
    }

    /**
     * Set postalcode2
     *
     * @param integer $postalcode2
     *
     * @return Contact
     */
    public function setPostalcode2 ($postalcode2)
    {
        $this->postalcode2 = $postalcode2;

        return $this;
    }

    /**
     * Get ipaddress
     * @return string
     */
    public function getIpaddress ()
    {
        return $this->ipaddress;
    }

    /**
     * Set ipaddress
     *
     * @param string $ipaddress
     *
     * @return Contact
     */
    public function setIpaddress ($ipaddress)
    {
        $this->ipaddress = $ipaddress;

        return $this;
    }

    /**
     * Get date
     * @return \DateTime
     */
    public function getDate ()
    {
        return $this->date;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Contact
     */
    public function setDate ($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get postRequest
     * @return array
     */
    public function getPostRequest ()
    {
        return $this->post_request;
    }

    /**
     * Set postRequest
     *
     * @param array $postRequest
     *
     * @return Contact
     */
    public function setPostRequest ($postRequest)
    {
        $this->post_request = $postRequest;

        return $this;
    }

    /**
     * Get validations
     * @return array
     */
    public function getValidations ()
    {
        return $this->validations;
    }

    /**
     * Set validations
     *
     * @param array $validations
     *
     * @return Contact
     */
    public function setValidations ($validations)
    {
        $this->validations = $validations;

        return $this;
    }

    /**
     * Add extractionContact
     *
     * @param ExtractionContact $extractionContact
     *
     * @return Contact
     */
    public function addExtractionContact (ExtractionContact $extractionContact)
    {
        $this->extraction_contacts[] = $extractionContact;

        return $this;
    }

    /**
     * Remove extractionContact
     *
     * @param ExtractionContact $extractionContact
     */
    public function removeExtractionContact (ExtractionContact $extractionContact)
    {
        $this->extraction_contacts->removeElement($extractionContact);
    }

    /**
     * Get extractionContacts
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExtractionContacts ()
    {
        return $this->extraction_contacts;
    }

    /**
     * Get lead
     * @return Lead
     */
    public function getLead ()
    {
        return $this->lead;
    }

    /**
     * Set lead
     *
     * @param Lead $lead
     *
     * @return Contact
     */
    public function setLead (Lead $lead)
    {
        $this->lead = $lead;

        return $this;
    }

    /**
     * Get source
     * @return Source
     */
    public function getSource ()
    {
        return $this->source;
    }

    /**
     * Set source
     *
     * @param Source $source
     *
     * @return Contact
     */
    public function setSource (Source $source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get owner
     * @return Owner
     */
    public function getOwner ()
    {
        return $this->owner;
    }

    /**
     * Set owner
     *
     * @param Owner $owner
     *
     * @return Contact
     */
    public function setOwner (Owner $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get subCategory
     * @return SubCategory
     */
    public function getSubCategory ()
    {
        return $this->sub_category;
    }

    /**
     * Set subCategory
     *
     * @param SubCategory $subCategory
     *
     * @return Contact
     */
    public function setSubCategory (SubCategory $subCategory)
    {
        $this->sub_category = $subCategory;

        return $this;
    }

    /**
     * Get gender
     * @return Gender
     */
    public function getGender ()
    {
        return $this->gender;
    }

    /**
     * Set gender
     *
     * @param Gender $gender
     *
     * @return Contact
     */
    public function setGender (Gender $gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get district
     * @return District
     */
    public function getDistrict ()
    {
        return $this->district;
    }

    /**
     * Set district
     *
     * @param District $district
     *
     * @return Contact
     */
    public function setDistrict (District $district = null)
    {
        $this->district = $district;

        return $this;
    }

    /**
     * Get county
     * @return County
     */
    public function getCounty ()
    {
        return $this->county;
    }

    /**
     * Set county
     *
     * @param County $county
     *
     * @return Contact
     */
    public function setCounty (County $county = null)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * Get parish
     * @return Parish
     */
    public function getParish ()
    {
        return $this->parish;
    }

    /**
     * Set parish
     *
     * @param Parish $parish
     *
     * @return Contact
     */
    public function setParish (Parish $parish = null)
    {
        $this->parish = $parish;

        return $this;
    }

    /**
     * Get country
     * @return Country
     */
    public function getCountry ()
    {
        return $this->country;
    }

    /**
     * Set country
     *
     * @param Country $country
     *
     * @return Contact
     */
    public function setCountry (Country $country)
    {
        $this->country = $country;

        return $this;
    }
}
