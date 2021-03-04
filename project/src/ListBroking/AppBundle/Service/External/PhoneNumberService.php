<?php

namespace ListBroking\AppBundle\Service\External;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberService implements PhoneNumberServiceInterface
{
    /**
     * @var PhoneNumberUtil
     */
    private $phoneUtil;

    /**
     * PhoneNumberService constructor.
     */
    public function __construct()
    {
        $this->phoneUtil = PhoneNumberUtil::getInstance();
    }

    /**
     * {@inheritDoc}
     *
     * @throws NumberParseException
     */
    public function getPhoneWithCountryCode(int $phone, string $region): ?string
    {
        $phoneNumber = $this->phoneUtil->parse($phone, $region);

        return sprintf(
            '%s%s',
            $phoneNumber->getCountryCode(),
            $phoneNumber->getNationalNumber()
        );
    }
}