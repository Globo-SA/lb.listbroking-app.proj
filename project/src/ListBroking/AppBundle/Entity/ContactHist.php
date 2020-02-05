<?php

namespace ListBroking\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * ContactHist
 */
class ContactHist
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $is_clean;

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
     * @var string
     */
    private $postalcode1;

    /**
     * @var string
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
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $extraction_contact_hist;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $contact_campaign_hist;

    /**
     * @var LeadHist
     */
    private $lead_hist;

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

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->extraction_contact_hist = new ArrayCollection();
        $this->contact_campaign_hist   = new ArrayCollection();
    }

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
     * Set isClean
     *
     * @param boolean $isClean
     *
     * @return ContactHist
     */
    public function setIsClean($isClean)
    {
        $this->is_clean = $isClean;

        return $this;
    }

    /**
     * Get isClean
     *
     * @return boolean
     */
    public function getIsClean()
    {
        return $this->is_clean;
    }

    /**
     * Set externalId
     *
     * @param string $externalId
     *
     * @return ContactHist
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
     * @return ContactHist
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
     * @return ContactHist
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
     * @return ContactHist
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
     * @return ContactHist
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
     * @return ContactHist
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
     * @param string $postalcode1
     *
     * @return ContactHist
     */
    public function setPostalcode1($postalcode1)
    {
        $this->postalcode1 = $postalcode1;

        return $this;
    }

    /**
     * Get postalcode1
     *
     * @return string
     */
    public function getPostalcode1()
    {
        return $this->postalcode1;
    }

    /**
     * Set postalcode2
     *
     * @param string $postalcode2
     *
     * @return ContactHist
     */
    public function setPostalcode2($postalcode2)
    {
        $this->postalcode2 = $postalcode2;

        return $this;
    }

    /**
     * Get postalcode2
     *
     * @return string
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
     * @return ContactHist
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
     * @return ContactHist
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
     * @return ContactHist
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
     * @return ContactHist
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ContactHist
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return ContactHist
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Add extractionContactHist
     *
     * @param ExtractionContactHist $extractionContactHist
     *
     * @return ContactHist
     */
    public function addExtractionContactHist(ExtractionContactHist $extractionContactHist)
    {
        $this->extraction_contact_hist[] = $extractionContactHist;

        return $this;
    }

    /**
     * Remove extractionContactHist
     *
     * @param ExtractionContactHist $extractionContactHist
     */
    public function removeExtractionContactHist(ExtractionContactHist $extractionContactHist)
    {
        $this->extraction_contact_hist->removeElement($extractionContactHist);
    }

    /**
     * Get extractionContactHist
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExtractionContactHist()
    {
        return $this->extraction_contact_hist;
    }

    /**
     * Add contactCampaignHist
     *
     * @param ContactCampaignHist $contactCampaignHist
     *
     * @return ContactHist
     */
    public function addContactCampaignHist(ContactCampaignHist $contactCampaignHist)
    {
        $this->contact_campaign_hist[] = $contactCampaignHist;

        return $this;
    }

    /**
     * Remove contactCampaignHist
     *
     * @param ContactCampaignHist $contactCampaignHist
     */
    public function removeContactCampaignHist(ContactCampaignHist $contactCampaignHist)
    {
        $this->contact_campaign_hist->removeElement($contactCampaignHist);
    }

    /**
     * Get contactCampaignHist
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContactCampaignHist()
    {
        return $this->contact_campaign_hist;
    }

    /**
     * Set leadHist
     *
     * @param LeadHist $lead_hist
     *
     * @return ContactHist
     */
    public function setLeadHist(LeadHist $lead_hist)
    {
        $this->lead_hist = $lead_hist;

        return $this;
    }

    /**
     * Get leadHist
     *
     * @return LeadHist
     */
    public function getLeadHist()
    {
        return $this->lead_hist;
    }

    /**
     * Set source
     *
     * @param Source $source
     *
     * @return ContactHist
     */
    public function setSource(Source $source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set owner
     *
     * @param Owner $owner
     *
     * @return ContactHist
     */
    public function setOwner(Owner $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return Owner
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set subCategory
     *
     * @param SubCategory $subCategory
     *
     * @return ContactHist
     */
    public function setSubCategory(SubCategory $subCategory)
    {
        $this->sub_category = $subCategory;

        return $this;
    }

    /**
     * Get subCategory
     *
     * @return SubCategory
     */
    public function getSubCategory()
    {
        return $this->sub_category;
    }

    /**
     * Set gender
     *
     * @param Gender $gender
     *
     * @return ContactHist
     */
    public function setGender(Gender $gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return Gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set district
     *
     * @param District $district
     *
     * @return ContactHist
     */
    public function setDistrict(District $district = null)
    {
        $this->district = $district;

        return $this;
    }

    /**
     * Get district
     *
     * @return District
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Set county
     *
     * @param County $county
     *
     * @return ContactHist
     */
    public function setCounty(County $county = null)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * Get county
     *
     * @return County
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Set parish
     *
     * @param Parish $parish
     *
     * @return ContactHist
     */
    public function setParish(Parish $parish = null)
    {
        $this->parish = $parish;

        return $this;
    }

    /**
     * Get parish
     *
     * @return Parish
     */
    public function getParish()
    {
        return $this->parish;
    }

    /**
     * Set country
     *
     * @param Country $country
     *
     * @return ContactHist
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }
}

