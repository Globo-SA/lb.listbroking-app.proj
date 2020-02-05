<?php

namespace ListBroking\AppBundle\Entity;

/**
 * ContactCampaignHist
 */
class ContactCampaignHist
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var ContactHist
     */
    private $contact;

    /**
     * @var Campaign
     */
    private $campaign;


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
     * @return ContactCampaignHist
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
     * Set contact
     *
     * @param ContactHist $contact
     *
     * @return ContactCampaignHist
     */
    public function setContact(ContactHist $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return ContactHist
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set campaign
     *
     * @param Campaign $campaign
     *
     * @return ContactCampaignHist
     */
    public function setCampaign(Campaign $campaign = null)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Get campaign
     *
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }
}

