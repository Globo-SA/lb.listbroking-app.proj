<?php

namespace ListBroking\AppBundle\Entity;

/**
 * ExtractionDeduplication
 *
 * NOTE: This entity doesn't have associations, it's only
 * used to persist deduplications by field:
 *          . phone
 */
class ExtractionDeduplication
{
    const CACHE_ID = 'extraction_deduplication';

    /**
     * Extraction Deduplication by phone number
     */
    const TYPE_PHONE = 'phone';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $lead_id;

    /**
     * @var int
     */
    protected $contact_id;

    /**
     * @var Extraction
     */
    protected $extraction;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @var \DateTime
     */
    protected $createdAt;

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
     * @return int
     */
    public function getContactId()
    {
        return $this->contact_id;
    }

    /**
     * @param int $contact_id
     */
    public function setContactId($contact_id)
    {
        $this->contact_id = $contact_id;
    }

    /**
     * @return int
     */
    public function getLeadId()
    {
        return $this->lead_id;
    }

    /**
     * @param int $lead_id
     */
    public function setLeadId($lead_id)
    {
        $this->lead_id = $lead_id;
    }

    /**
     * @return Extraction
     */
    public function getExtraction()
    {
        return $this->extraction;
    }

    /**
     * @param Extraction $extraction
     */
    public function setExtraction(Extraction $extraction)
    {
        $this->extraction = $extraction;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
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
