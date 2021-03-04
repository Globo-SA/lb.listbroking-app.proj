<?php

namespace ListBroking\AppBundle\Service\External;

interface PhoneNumberServiceInterface
{
    /**
     * Returns a phone number with callsign considering a given region
     *
     * @param int    $phone
     * @param string $region
     *
     * @return mixed
     */
    public function getPhoneWithCountryCode(int $phone, string $region): ?string;
}