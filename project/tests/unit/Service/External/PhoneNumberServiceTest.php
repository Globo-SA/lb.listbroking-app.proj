<?php

namespace ListBroking\Tests\Unit\Service\External;

use ListBroking\AppBundle\Service\External\PhoneNumberService;
use PHPUnit\Framework\TestCase;

class PhoneNumberServiceTest extends TestCase
{
    /**
     * @var PhoneNumberService
     */
    private $phoneNumberService;

    /**
     * PhoneNumberServiceTest constructor.
     *
     * @param null   $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->phoneNumberService = new PhoneNumberService();
    }

    public function testGettingPTCountryCode()
    {
        $phoneWithCountryCode = $this->phoneNumberService->getPhoneWithCountryCode('919876543', 'PT');

        $this->assertEquals('351919876543', $phoneWithCountryCode);
    }

    public function testGettingESCountryCode()
    {
        $phoneWithCountryCode = $this->phoneNumberService->getPhoneWithCountryCode('919876543', 'ES');

        $this->assertEquals('34919876543', $phoneWithCountryCode);
    }

    public function testGettingExceptionFromInvalidRegion()
    {
        $this->expectException('libphonenumber\NumberParseException');

        $this->phoneNumberService->getPhoneWithCountryCode('919876543', 'INVALID_REGION');
    }

    public function testGettingExceptionFromInvalidNumber()
    {
        $this->expectException('libphonenumber\NumberParseException');

        $this->phoneNumberService->getPhoneWithCountryCode('000000000000000', 'PT');
    }
}
