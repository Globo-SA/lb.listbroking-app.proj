<?php

namespace ListBroking\AppBundle\Enum;

/**
 * ExtractionFieldsEnum
 */
class ExtractionFieldsEnum
{
    const FIRSTNAME   = 'firstname';
    const LASTNAME    = 'lastname';
    const PHONE       = 'phone';
    const EMAIL       = 'email';
    const BIRTHDATE   = 'birthdate';
    const AGE         = 'age';
    const GENDER      = 'gender';
    const DISTRICT    = 'district';
    const ADDRESS     = 'address';
    const POSTALCODE1 = 'postalcode1';
    const POSTALCODE2 = 'postalcode2';
    const IPADDRESS   = 'ipaddress';

    /**
     * @return array|string[]
     */
    public static function getAll(): array
    {
        return [
            static::FIRSTNAME,
            static::LASTNAME,
            static::PHONE,
            static::EMAIL,
            static::BIRTHDATE,
            static::AGE,
            static::GENDER,
            static::DISTRICT,
            static::ADDRESS,
            static::POSTALCODE1,
            static::POSTALCODE2,
            static::IPADDRESS,
        ];
    }
}
