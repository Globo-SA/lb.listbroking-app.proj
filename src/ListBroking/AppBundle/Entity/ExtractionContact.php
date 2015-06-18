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
     * @var \ListBroking\AppBundle\Entity\Extraction
     */
    private $extraction;

    /**
     * @var \ListBroking\AppBundle\Entity\Contact
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
     * Set extraction
     *
     * @param \ListBroking\AppBundle\Entity\Extraction $extraction
     *
     * @return ExtractionContact
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
     * @param \ListBroking\AppBundle\Entity\Contact $contact
     *
     * @return ExtractionContact
     */
    public function setContact(\ListBroking\AppBundle\Entity\Contact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \ListBroking\AppBundle\Entity\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }
}
