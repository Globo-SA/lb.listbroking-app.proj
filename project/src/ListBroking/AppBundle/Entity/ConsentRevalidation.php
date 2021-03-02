<?php

namespace ListBroking\AppBundle\Entity;

use DateTime;

/**
 * ConsentRevalidation
 */
class ConsentRevalidation
{
    public const TYPE_IVR        = 'IVR';
    public const STATUS_NEW      = 'new';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $data;

    /**
     * @var Contact
     */
    private $contact;

    /**
     * @var DateTime
     */
    private $createdAt;

    /**
     * @var DateTime
     */
    private $updatedAt;

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
     * Set type
     *
     * @param string $type
     *
     * @return ConsentRevalidation
     */
    public function setType(string $type): ConsentRevalidation
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData(string $data): void
    {
        $this->data = $data;
    }

    /**
     * @param Contact $contact
     */
    public function setContact(Contact $contact): void
    {
        $this->contact = $contact;
    }

    /**
     * @return Contact
     */
    public function getContact(): Contact
    {
        return $this->contact;
    }

    /**
     * Set createdAt
     *
     * @param DateTime $createdAt
     *
     * @return ConsentRevalidation
     */
    public function setCreatedAt(DateTime $createdAt): ConsentRevalidation
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param DateTime $updatedAt
     *
     * @return ConsentRevalidation
     */
    public function setUpdatedAt(DateTime $updatedAt): ConsentRevalidation
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param array $data
     */
    public function setDataAsArray(array $data): void
    {
        $this->data = json_encode($data);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getDataByKey(string $key): string
    {
        $dataDecoded = json_decode($this->data, true);

        return $dataDecoded[$key] ?? '';
    }
}

