<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Entity;

/**
 * ListBroking\AppBundle\Entity\ExtractionContact
 */
class ExtractionContact
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Extraction
     */
    protected $extraction;

    /**
     * @var Contact
     */
    protected $contact;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * OppositionList constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * Get extraction
     *
     * @return Extraction
     */
    public function getExtraction()
    {
        return $this->extraction;
    }

    /**
     * Set extraction
     *
     * @param Extraction $extraction
     *
     * @return ExtractionContact
     */
    public function setExtraction(Extraction $extraction = null)
    {
        $this->extraction = $extraction;

        return $this;
    }

    /**
     * Get contact
     *
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set contact
     *
     * @param Contact $contact
     *
     * @return ExtractionContact
     */
    public function setContact(Contact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get Created At
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set Created At
     *
     * @param \DateTime $createdAt
     *
     * @return ExtractionDeduplication
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
