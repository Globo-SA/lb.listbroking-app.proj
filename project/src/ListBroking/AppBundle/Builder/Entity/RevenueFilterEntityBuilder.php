<?php

namespace ListBroking\AppBundle\Builder\Entity;

use ListBroking\AppBundle\Entity\RevenueFilter;

class RevenueFilterEntityBuilder
{
    const MONTH_FIRST_DAY_FORMAT = 'Y-m-01';
    const CURRENT_DAY_FORMAT     = 'Y-m-t';

    /**
     * @var RevenueFilter
     */
    private $revenueFilter;

    /**
     * @var false|null|string
     */
    private $startDate;

    /**
     * @var false|null|string
     */
    private $endDate;

    /**
     * @var array|null
     */
    private $excludedOwners;

    /**
     * RevenueFilterModelBuilder constructor.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param array|null  $excludedOwners
     */
    public function __construct(string $startDate = null, string $endDate = null, array $excludedOwners = null)
    {
        $this->revenueFilter  = new RevenueFilter();
        $this->startDate      = $startDate ?? date(self::MONTH_FIRST_DAY_FORMAT);
        $this->endDate        = $endDate ?? date(self::CURRENT_DAY_FORMAT);
        $this->excludedOwners = $excludedOwners ?? [];
    }

    /**
     * Builds the RevenueFilterModel.
     */
    public function build(): void
    {
        $this->revenueFilter->setStartDate($this->startDate);
        $this->revenueFilter->setEndDate($this->endDate);
        $this->revenueFilter->setExcludedOwners($this->excludedOwners);
    }

    /**
     * Get a RevenueFilterModel object.
     *
     * @return RevenueFilter
     */
    public function getRevenueFilter(): RevenueFilter
    {
        return $this->revenueFilter;
    }
}