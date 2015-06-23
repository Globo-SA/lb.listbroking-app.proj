<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */

namespace ListBroking\AppBundle\Entity;

class ExtractionContact
{

    // GENERATED STUFF

    /**
     * @var integer
     */
    private $id;

    /**
     * @var Extraction
     */
    private $extraction;

    /**
     * @var Contact
     */
    private $contact;

    /**
     * Get id
     * @return integer
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * Get extraction
     * @return Extraction
     */
    public function getExtraction ()
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
    public function setExtraction (Extraction $extraction = null)
    {
        $this->extraction = $extraction;

        return $this;
    }

    /**
     * Get contact
     * @return Contact
     */
    public function getContact ()
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
    public function setContact (Contact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }
}
