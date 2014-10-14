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

class DistrictValidator extends BaseValidator {
    /**
     * @param $service
     * @param Request $request
     */
    public function __construct($service, Request $request)
    {
        parent::__construct($service, $request);
    }

    /**
     * @param $validations
     * @return mixed
     */
    public function validate($validations){
        if (isset($this->lead['district'])){
            $validations['district'] = $this->lead['district'];
        } else {
            $validations['district'] = null;
        }
        return $validations;
    }
} 