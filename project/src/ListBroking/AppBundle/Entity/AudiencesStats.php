<?php

namespace ListBroking\AppBundle\Entity;

/**
 * AudiencesStats
 */
class AudiencesStats
{
    /**
     * @var int
     */
    private $id;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @var string
     */
    private $gender;

    /**
     * @var int
     */
    private $age;

    /**
     * @var boolean
     */
    private $is_mobile;

    /**
     * @var int
     */
    private $total;

    /**
     * @var \ListBroking\AppBundle\Entity\Owner
     */
    private $owner;

    /**
     * @var \ListBroking\AppBundle\Entity\Country
     */
    private $country;

    /**
     * @var \ListBroking\AppBundle\Entity\District
     */
    private $district;

    /**
     * @var \ListBroking\AppBundle\Entity\SubCategory
     */
    private $sub_category;


    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return AudiencesStats
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set age
     *
     * @param int $age
     *
     * @return AudiencesStats
     */
    public function setAge(int $age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age
     *
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set isMobile
     *
     * @param boolean $isMobile
     *
     * @return AudiencesStats
     */
    public function setIsMobile($isMobile)
    {
        $this->is_mobile = $isMobile;

        return $this;
    }

    /**
     * Get isMobile
     *
     * @return boolean
     */
    public function getIsMobile()
    {
        return $this->is_mobile;
    }

    /**
     * Set total
     *
     * @param int $total
     *
     * @return AudiencesStats
     */
    public function setTotal(int $total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set country
     *
     * @param \ListBroking\AppBundle\Entity\Country $country
     *
     * @return AudiencesStats
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

    /**
     * Set district
     *
     * @param \ListBroking\AppBundle\Entity\District $district
     *
     * @return AudiencesStats
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
     * Set subCategory
     *
     * @param \ListBroking\AppBundle\Entity\SubCategory $subCategory
     *
     * @return AudiencesStats
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
     * Set owner
     *
     * @param \ListBroking\AppBundle\Entity\Owner $owner
     *
     * @return AudiencesStats
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
}
