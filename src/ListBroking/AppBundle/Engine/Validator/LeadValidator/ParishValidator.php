<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Validator\LeadValidator;



class ParishValidator extends BaseValidator {
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
        if (isset($this->lead['parish'])){
            $validations['parish'] = $this->service->getParishByName($this->lead['parish'], true);
        } else {
            $validations['parish'] = null;
        }
        return $validations;
    }
} 