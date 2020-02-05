<?php

namespace ListBroking\AppBundle\Entity;

class RevenueFilter
{
    /**
     * @var string
     */
    protected $startDate;

    /**
     * @var string
     */
    protected $endDate;

    /**
     * @var array
     */
    protected $excludedOwners;

    /**
     * @param string $startDate
     */
    public function setStartDate(string $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @param string $endDate
     */
    public function setEndDate(string $endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @param array $excludedOwners
     */
    public function setExcludedOwners(array $excludedOwners): void
    {
        $this->excludedOwners = $excludedOwners;
    }

    /**
     * Returns de filter's start date.
     *
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * Returns the filter's end date.
     *
     * @return string
     */
    public function getEndDate(): string
    {
        return $this->endDate;
    }

    /**
     * Returns an array with the filter's excluded owners.
     *
     * @return array
     */
    public function getExcludedOwners(): array
    {
        return $this->excludedOwners;
    }
}