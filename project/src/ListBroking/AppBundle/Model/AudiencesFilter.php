<?php

namespace ListBroking\AppBundle\Model;

/**
 * AudiencesFilter
 */
class AudiencesFilter
{
    public const FILTER                     = 'filter';
    public const FILTER_OWNER               = 'owner';
    public const FILTER_COUNTRY             = 'country';
    public const FILTER_INCLUDED_DISTRICTS  = 'included_districts';
    public const FILTER_EXCLUDED_DISTRICTS  = 'excluded_districts';
    public const FILTER_INCLUDED_CATEGORIES = 'included_categories';
    public const FILTER_EXCLUDED_CATEGORIES = 'excluded_categories';
    public const FILTER_GENDER              = 'gender';
    public const FILTER_MIN_AGE             = 'min_age';
    public const FILTER_MAX_AGE             = 'max_age';
    public const FILTER_IS_MOBILE           = 'is_mobile';

    public const FIELDS          = 'fields';
    public const FIELD_DISTRICT  = 'district';
    public const FIELD_GENDER    = 'gender';
    public const FIELD_CATEGORY  = 'category';
    public const FIELD_AGE       = 'age';
    public const FIELD_IS_MOBILE = 'is_mobile';

    private const FILTER_ALLOWED_VALUE_GENDER       = ['M', 'F', 'NA'];
    private const FILTER_ALLOWED_VALUE_SIZE_COUNTRY = 2;

    /**
     * @var string
     */
    private $owner;

    /**
     * @var string
     */
    private $country;

    /**
     * @var array
     */
    private $includedDistricts;

    /**
     * @var array
     */
    private $excludedDistricts;

    /**
     * @var array
     */
    private $includedCategories;

    /**
     * @var array
     */
    private $excludedCategories;

    /**
     * @var array
     */
    private $gender;

    /**
     * @var int
     */
    private $minAge;

    /**
     * @var int
     */
    private $maxAge;

    /**
     * @var bool
     */
    private $isMobile;

    /**
     * @var array
     */
    protected $invalidations;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @return string
     */
    public function getOwner(): ?string
    {
        return $this->owner;
    }

    /**
     * @param string $owner
     *
     * @return AudiencesFilter
     */
    public function setOwner(?string $owner): AudiencesFilter
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return AudiencesFilter
     */
    public function setCountry(?string $country): AudiencesFilter
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return array
     */
    public function getIncludedDistricts(): ?array
    {
        return $this->includedDistricts;
    }

    /**
     * @param array $includedDistricts
     *
     * @return AudiencesFilter
     */
    public function setIncludedDistricts(?array $includedDistricts): AudiencesFilter
    {
        $this->includedDistricts = $includedDistricts;

        return $this;
    }

    /**
     * @return array
     */
    public function getExcludedDistricts(): ?array
    {
        return $this->excludedDistricts;
    }

    /**
     * @param array $excludedDistricts
     *
     * @return AudiencesFilter
     */
    public function setExcludedDistricts(?array $excludedDistricts): AudiencesFilter
    {
        $this->excludedDistricts = $excludedDistricts;

        return $this;
    }

    /**
     * @return array
     */
    public function getIncludedCategories(): ?array
    {
        return $this->includedCategories;
    }

    /**
     * @param array $includedCategories
     *
     * @return AudiencesFilter
     */
    public function setIncludedCategories(?array $includedCategories): AudiencesFilter
    {
        $this->includedCategories = $includedCategories;

        return $this;
    }

    /**
     * @return array
     */
    public function getExcludedCategories(): ?array
    {
        return $this->excludedCategories;
    }

    /**
     * @param array $excludedCategories
     *
     * @return AudiencesFilter
     */
    public function setExcludedCategories(?array $excludedCategories): AudiencesFilter
    {
        $this->excludedCategories = $excludedCategories;

        return $this;
    }

    /**
     * @return array
     */
    public function getGender(): ?array
    {
        return $this->gender;
    }

    /**
     * @param array $gender
     *
     * @return AudiencesFilter
     */
    public function setGender(?array $gender): AudiencesFilter
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinAge(): ?int
    {
        return $this->minAge;
    }

    /**
     * @param int $minAge
     *
     * @return AudiencesFilter
     */
    public function setMinAge(?int $minAge): AudiencesFilter
    {
        $this->minAge = $minAge;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    /**
     * @param int $maxAge
     *
     * @return AudiencesFilter
     */
    public function setMaxAge(?int $maxAge): AudiencesFilter
    {
        $this->maxAge = $maxAge;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsMobile(): ?bool
    {
        return $this->isMobile;
    }

    /**
     * @param bool $isMobile
     *
     * @return AudiencesFilter
     */
    public function setIsMobile(?bool $isMobile): AudiencesFilter
    {
        $this->isMobile = $isMobile;

        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * @param array|null $fields
     *
     * @return AudiencesFilter
     */
    public function setFields(?array $fields): AudiencesFilter
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Check if requested filters are valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        $this->validateAudiencesFields();
        $this->validateCountry();
        $this->validateGender();
        $this->validateMinAge();
        $this->validateMaxAge();
        $this->validateIsMobile();

        return empty($this->invalidations);
    }

    /**
     * @return array
     */
    public function getInvalidations(): array
    {
        return $this->invalidations;
    }

    /**
     * Build AudiencesFilter object based on Request
     *
     * @param array|null $requestFilter
     * @param array|null $requestFields
     *
     * @return AudiencesFilter
     */
    public static function buildAudiencesFilterFromRequest(
        ?array $requestFilter,
        ?array $requestFields
    ): AudiencesFilter {
        $filter = new AudiencesFilter();

        $filter->setOwner($requestFilter[self::FILTER_OWNER] ?? null)
               ->setCountry($requestFilter[self::FILTER_COUNTRY] ?? null)
               ->setIncludedDistricts($requestFilter[self::FILTER_INCLUDED_DISTRICTS] ?? null)
               ->setExcludedDistricts($requestFilter[self::FILTER_EXCLUDED_DISTRICTS] ?? null)
               ->setIncludedCategories($requestFilter[self::FILTER_INCLUDED_CATEGORIES] ?? null)
               ->setExcludedCategories($requestFilter[self::FILTER_EXCLUDED_CATEGORIES] ?? null)
               ->setGender($requestFilter[self::FILTER_GENDER] ?? null)
               ->setMinAge($requestFilter[self::FILTER_MIN_AGE] ?? null)
               ->setMaxAge($requestFilter[self::FILTER_MAX_AGE] ?? null)
               ->setIsMobile($requestFilter[self::FILTER_IS_MOBILE] ?? null);

        $filter->setFields($requestFields);

        return $filter;
    }

    /**
     * Validates Country Code Format
     */
    protected function validateCountry(): void
    {
        if (strlen($this->country) !== self::FILTER_ALLOWED_VALUE_SIZE_COUNTRY) {
            $this->invalidations[self::FILTER_COUNTRY] = sprintf(
                'must be a valid Iso Code with %s characters',
                self::FILTER_ALLOWED_VALUE_SIZE_COUNTRY
            );
        }
    }

    /**
     * Validates Gender Allowed Values
     */
    protected function validateGender(): void
    {
        if ($this->gender !== null && is_array($this->gender)) {
            foreach ($this->gender as $gender) {
                if (!in_array($gender, self::FILTER_ALLOWED_VALUE_GENDER)) {
                    $this->invalidations[self::FILTER_GENDER] = sprintf(
                        'allowed values (%s)',
                        implode(', ', self::FILTER_ALLOWED_VALUE_GENDER)
                    );

                    break;
                }
            }
        }
    }

    /**
     * Validates Min Age Format
     */
    protected function validateMinAge(): void
    {
        if ($this->minAge !== null && !is_int($this->minAge) && $this->minAge < 18 && $this->minAge > 100) {
            $this->invalidations[self::FILTER_MIN_AGE] = 'must be an integer value above 18';
        }
    }

    /**
     * Validates Max Age Format
     */
    protected function validateMaxAge(): void
    {
        if ($this->maxAge !== null && !is_int($this->maxAge) && $this->maxAge < 18 && $this->maxAge > 100) {
            $this->invalidations[self::FILTER_MAX_AGE] = 'must be an integer value above 18';
        }
    }

    /**
     * Validates Is Mobile
     */
    protected function validateIsMobile(): void
    {
        if ($this->isMobile !== null && !in_array($this->isMobile, [0, 1])) {
            $this->invalidations[self::FIELD_IS_MOBILE] = 'must be 0 or 1';
        }
    }

    /**
     * Validates Allowed Fields
     */
    protected function validateAudiencesFields(): void
    {
        if ($this->fields === null || !is_array($this->fields)) {
            return;
        }

        $fieldAllowedValues = [
            self::FIELD_AGE,
            self::FIELD_CATEGORY,
            self::FIELD_DISTRICT,
            self::FIELD_GENDER,
            self::FIELD_IS_MOBILE,
        ];

        foreach ($this->fields as $field) {
            if (in_array($field, $fieldAllowedValues)) {
                continue;
            }

            $this->invalidations[self::FIELDS] = sprintf(
                'allowed values (%s)',
                implode(', ', $fieldAllowedValues)
            );

            break;
        }
    }
}
