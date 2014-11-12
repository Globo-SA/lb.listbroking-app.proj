<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\CoreBundle\Engine\CoreValidator;


use ListBroking\CoreBundle\Exception\CoreValidationException;

class CountryValidator extends BaseValidator {

    public function __construct($service, $lead)
    {
        parent::__construct($service, $lead);
        $this->countries = array(           //TODO: FILL THIS WITH ENTRIES FROM DATABASE COUNTRIES TABLE WITH THIS FORMAT
            'PT' => 'Portugal',
            'FR' => 'France'
        );
    }

    public function validate($validations)
    {
        if (!isset($this->lead['country']) || strlen($this->lead['country'])>2){
            throw new CoreValidationException("Field lead[country] not sent. \r\n");
        }

        parent::validateEmpty($this->lead['country'], 'country');
        $country_code = strtoupper($this->lead['country']);
        $validations['country'] = $this->service->getCountryByCode($country_code, true);
        return $validations;
    }
}