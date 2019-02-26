<?php

namespace ListBroking\AppBundle\Entity;

use ListBroking\AppBundle\Behavior\TimestampableEntityBehavior;

/**
 * ContactCampaign
 */
class ContactCampaign
{
    use TimestampableEntityBehavior;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $contact_id;

    /**
     * @var int
     */
    private $campaign_id;

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
     * Set contactId
     *
     * @param integer $contactId
     *
     * @return ContactCampaign
     */
    public function setContactId($contactId)
    {
        $this->contact_id = $contactId;

        return $this;
    }

    /**
     * Get contactId
     *
     * @return int
     */
    public function getContactId()
    {
        return $this->contact_id;
    }

    /**
     * Set campaignId
     *
     * @param integer $campaignId
     *
     * @return ContactCampaign
     */
    public function setCampaignId($campaignId)
    {
        $this->campaign_id = $campaignId;

        return $this;
    }

    /**
     * Get campaignId
     *
     * @return int
     */
    public function getCampaignId()
    {
        return $this->campaign_id;
    }

    /**
     * @var Contact
     */
    private $contact;

    /**
     * @var Campaign
     */
    private $campaign;


    /**
     * Set contact
     *
     * @param Contact $contact
     *
     * @return ContactCampaign
     */
    public function setContact(Contact $contact = null)
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

    /**
     * Set campaign
     *
     * @param Campaign $campaign
     *
     * @return ContactCampaign
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
