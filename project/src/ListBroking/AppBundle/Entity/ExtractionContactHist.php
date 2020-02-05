<?php

namespace ListBroking\AppBundle\Entity;

/**
 * ExtractionContactHist
 */
class ExtractionContactHist
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createdAt = 0;

    /**
     * @var \ListBroking\AppBundle\Entity\Extraction
     */
    private $extraction;

    /**
     * @var \ListBroking\AppBundle\Entity\ContactHist
     */
    private $contact;


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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ExtractionContactHist
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set extraction
     *
     * @param \ListBroking\AppBundle\Entity\Extraction $extraction
     *
     * @return ExtractionContactHist
     */
    public function setExtraction(\ListBroking\AppBundle\Entity\Extraction $extraction = null)
    {
        $this->extraction = $extraction;

        return $this;
    }

    /**
     * Get extraction
     *
     * @return \ListBroking\AppBundle\Entity\Extraction
     */
    public function getExtraction()
    {
        return $this->extraction;
    }

    /**
     * Set contact
     *
     * @param \ListBroking\AppBundle\Entity\ContactHist $contact
     *
     * @return ExtractionContactHist
     */
    public function setContact(\ListBroking\AppBundle\Entity\ContactHist $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \ListBroking\AppBundle\Entity\ContactHist
     */
    public function getContact()
    {
        return $this->contact;
    }
}

