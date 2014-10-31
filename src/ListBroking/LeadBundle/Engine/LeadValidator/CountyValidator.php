<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LeadBundle\Engine\LeadValidator;


use Symfony\Component\HttpFoundation\Request;

class CountyValidator extends BaseValidator {
    /**
     * @param $service
     * @param $lead
     */
    public function __construct($service, $lead)
    {
        parent::__construct($service, $lead);
    }

    /**
     * @param $validations
     * @return mixed
     */
    public function validate($validations){
        if (isset($this->lead['county'])){
            $validations['county'] = $this->service->getCountyByName($this->lead['county'], true);
        } else {
            $validations['county'] = null;
        }
        return $validations;
    }
} 