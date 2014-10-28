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
use Symfony\Component\HttpFoundation\Request;

class CountryValidator extends BaseValidator {

    public function __construct($service, Request $request)
    {
        parent::__construct($service, $request);
        $this->countries = array(
            'PT' => 'Portugal',
            'FR' => 'France'
        );
    }

    public function validate($validations)
    {

        if (!isset($this->lead['country'])){
            throw new CoreValidationException("Field lead[country] not sent. \r\n");
        }

        parent::validateEmpty($this->lead['country'], 'country_code');
        $country_code = $this->lead['country'];
        $validations['country']['code'] = $country_code;
        $validations['country']['name'] = $this->countries[$country_code];
        $validations['country'] = $this->service->getCountryByCode($validations['country']['code'], true);
        return $validations;
    }
}