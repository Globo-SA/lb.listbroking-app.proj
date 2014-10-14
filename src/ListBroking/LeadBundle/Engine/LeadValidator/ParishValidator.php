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

class ParishValidator extends BaseValidator {
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
        if (isset($this->lead['parish'])){
            $validations['parish'] = $this->lead['parish'];
        } else {
            $validations['parish'] = null;
        }
        return $validations;
    }
} 