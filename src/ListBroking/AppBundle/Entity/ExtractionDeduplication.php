<?php

namespace ListBroking\AppBundle\Entity;

use ListBroking\AppBundle\Behavior\BlameableEntityBehavior;
use ListBroking\AppBundle\Behavior\TimestampableEntityBehavior;
use Doctrine\ORM\Mapping as ORM;

/**
 * ExtractionDeduplication
 *
 * NOTE: This entity doesn't have associations, it's only
 * used to persist deduplications by field:
 *          . lead_id
 *          . contact_id
 *          . phone
 *          . email
 */
class ExtractionDeduplication
{
    const CACHE_ID = 'extraction_deduplication';

    /**
     * @var integer
     */
    protected $id;

    protected $lead_id;

    protected $contact_id;

    protected $extraction;

    protected $phone;

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
     * @return mixed
     */
    public function getContactId()
    {
        return $this->contact_id;
    }

    /**
     * @param mixed $contact_id
     */
    public function setContactId($contact_id)
    {
        $this->contact_id = $contact_id;
    }

    /**
     * @return mixed
     */
    public function getLeadId()
    {
        return $this->lead_id;
    }

    /**
     * @param mixed $lead_id
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
}
