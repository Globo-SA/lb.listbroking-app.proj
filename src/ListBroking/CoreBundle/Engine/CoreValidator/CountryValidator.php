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
            'FR' => 'France',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'PL' => 'Poland',
            'BR' => 'Brazil',
            'MX' => 'Mexico',
            'AR' => 'Argentina',
            'CO' => 'Colombia',
            'VE' => 'Venezuela',
            'US' => 'USA',
            'PE' => 'Peru',
            'UY' => 'Uruguay',
            'GB' => 'United Kingdom',
            'CA' => 'Canada',
            'DE' => 'Germany',
            'ZA' => 'South Africa',
            'HN' => 'Honduras',
            'CL' => 'Chile',
            'BE' => 'Belgium',
            'PA' => 'Panama',
            'B2' => 'Belgium Flanders',
            'OM' => 'Oman'
        );
    }

    public function validate($validations)
    {
        if (!isset($this->lead['country'])){
            throw new CoreValidationException("Field lead[country] not sent. \r\n");
        }

        parent::validateEmpty($this->lead['country'], 'country');
        if (strlen($this->lead['country'])>2) {
            $key = array_search($this->lead['country'], $this->countries);
            if ($key){
                $country_code = $key;
            } else {
                throw new CoreValidationException("Country wasn't found in countries list. Add it to the list if it's a valid one");
            }
        } else {
            $country_code = strtoupper($this->lead['country']);
        }
        $validations['country'] = $this->service->getCountryByCode($country_code, true);
        if ($validations['country'] == null){
            throw new CoreValidationException("Country wasn't found in country table. Add it to the table if it's a valid one");
        }
        return $validations;
    }
}