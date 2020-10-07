<?php

namespace ListBroking\AppBundle\Model;

use ListBroking\AppBundle\Enum\ExtractionFieldsEnum;

/**
 * Class ExtractionFilter
 * @package ListBroking\AppBundle\Model
 */
class ExtractionFilter extends AudiencesFilter
{
    public const CAMPAIGN = 'campaign_id';
    public const NAME     = 'name';
    public const QUANTITY = 'quantity';
    public const PAYOUT   = 'payout';

    /**
     * @var int
     */
    private $campaignId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var float
     */
    private $payout;

    /**
     * @return int
     */
    public function getCampaignId(): ?int
    {
        return $this->campaignId;
    }

    /**
     * @param int $campaignId
     *
     * @return ExtractionFilter
     */
    public function setCampaignId(?int $campaignId): ExtractionFilter
    {
        $this->campaignId = $campaignId;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return ExtractionFilter
     */
    public function setName(?string $name): ExtractionFilter
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return ExtractionFilter
     */
    public function setQuantity(?int $quantity): ExtractionFilter
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return float
     */
    public function getPayout(): ?float
    {
        return $this->payout;
    }

    /**
     * @param float $payout
     *
     * @return ExtractionFilter
     */
    public function setPayout(?float $payout): ExtractionFilter
    {
        $this->payout = $payout;

        return $this;
    }

    /**
     * Check if requested filters are valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        $this->validateCountry();
        $this->validateGender();
        $this->validateMinAge();
        $this->validateMaxAge();
        $this->validateIsMobile();
        $this->validatePayout();
        $this->validateQuantity();

        return empty($this->invalidations);
    }


    /**
     * Build AudiencesFilter object based on Request
     *
     * @param array|null $requestFilter
     *
     * @return ExtractionFilter
     */
    public static function buildExtractionFilterFromRequest(
        ?array $requestFilter
    ): ExtractionFilter {
        $filter = new ExtractionFilter();

        $filter->setCampaignId($requestFilter[self::CAMPAIGN] ?? null)
               ->setName($requestFilter[self::NAME] ?? null)
               ->setPayout($requestFilter[self::PAYOUT] ?? null)
               ->setQuantity($requestFilter[self::QUANTITY] ?? null)
               ->setOwner($requestFilter[self::FILTER][self::FILTER_OWNER] ?? null)
               ->setCountry($requestFilter[self::FILTER][self::FILTER_COUNTRY] ?? null)
               ->setIncludedDistricts($requestFilter[self::FILTER][self::FILTER_INCLUDED_DISTRICTS] ?? null)
               ->setExcludedDistricts($requestFilter[self::FILTER][self::FILTER_EXCLUDED_DISTRICTS] ?? null)
               ->setIncludedCategories($requestFilter[self::FILTER][self::FILTER_INCLUDED_CATEGORIES] ?? null)
               ->setExcludedCategories($requestFilter[self::FILTER][self::FILTER_EXCLUDED_CATEGORIES] ?? null)
               ->setGender($requestFilter[self::FILTER][self::FILTER_GENDER] ?? null)
               ->setMinAge($requestFilter[self::FILTER][self::FILTER_MIN_AGE] ?? null)
               ->setMaxAge($requestFilter[self::FILTER][self::FILTER_MAX_AGE] ?? null)
               ->setIsMobile($requestFilter[self::FILTER][self::FILTER_IS_MOBILE] ?? null);

        return $filter;
    }

    /**
     * Validates Payout Format
     */
    protected function validatePayout(): void
    {
        if (!is_float($this->getPayout())) {
            $this->invalidations[self::PAYOUT] = 'must be a valid real number';
        }
    }

    /**
     * Validate Quantity Format
     */
    protected function validateQuantity(): void
    {
        if (!is_int($this->getQuantity())) {
            $this->invalidations[self::QUANTITY] = 'must be a valid int number';
        }
    }

    /**
     * Validates Allowed Fields
     */
    protected function validateExtractionFields(): void
    {
        $fieldAllowedValues = ExtractionFieldsEnum::getAll();

        if ($this->fields !== null && is_array($this->fields)) {
            foreach ($this->fields as $field) {
                if (!in_array($field, $fieldAllowedValues)) {
                    $this->invalidations[self::FIELDS] = sprintf(
                        'allowed values (%s)',
                        implode(', ', $fieldAllowedValues)
                    );

                    break;
                }
            }
        }
    }
}
