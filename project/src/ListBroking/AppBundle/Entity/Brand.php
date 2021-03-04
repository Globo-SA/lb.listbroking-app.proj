<?php

namespace ListBroking\AppBundle\Entity;

use DateTime;

/**
 * Brand
 */
class Brand
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $ivrAudioUrl;

    /**
     * @var DateTime
     */
    private $createdAt;

    /**
     * @var DateTime
     */
    private $updatedAt;

    /**
     * @var array
     */
    private $sources;

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Brand
     */
    public function setName(string $name): Brand
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set ivrAudioUrl
     *
     * @param string $ivrAudioUrl
     *
     * @return Brand
     */
    public function setIvrAudioUrl(string $ivrAudioUrl): Brand
    {
        $this->ivrAudioUrl = $ivrAudioUrl;

        return $this;
    }

    /**
     * Get ivrAudioUrl
     *
     * @return mixed
     */
    public function getIvrAudioUrl()
    {
        return $this->ivrAudioUrl;
    }

    /**
     * @return mixed
     */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime $updatedAt
     */
    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return string
     */
    public function __toString ()
    {
        return $this->name;
    }
}

