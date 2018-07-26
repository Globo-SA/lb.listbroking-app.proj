<?php

namespace ListBroking\AppBundle\Entity;

/**
 * ClientNotification
 */
class ClientNotification
{
    const TYPE_RIGHT_TO_BE_FORGOTTEN = 'right_to_be_forgotten';

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $client;

    /**
     * @var int
     */
    private $lead;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $campaigns;

    /**
     * @var string
     */
    private $createdAt;

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
     * Set type
     *
     * @param string $type
     *
     * @return ClientNotification
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set campaigns
     *
     * @param string $campaigns
     *
     * @return ClientNotification
     */
    public function setCampaigns($campaigns)
    {
        $this->campaigns = $campaigns;

        return $this;
    }

    /**
     * Get campaigns
     *
     * @return string
     */
    public function getCampaigns()
    {
        return $this->campaigns;
    }

    /**
     * Set client
     *
     * @param mixed $client
     *
     * @return ClientNotification
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get clientId
     *
     * @return int
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set lead
     *
     * @param mixed $lead
     *
     * @return ClientNotification
     */
    public function setLead($lead)
    {
        $this->lead = $lead;

        return $this;
    }

    /**
     * Get leadId
     *
     * @return int
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * Set createdAt
     *
     * @param $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}

