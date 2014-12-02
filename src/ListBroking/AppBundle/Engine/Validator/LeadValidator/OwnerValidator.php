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


use ListBroking\AppBundle\Exception\LeadValidationException;

class OwnerValidator extends BaseValidator {
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
     * @throws LeadValidationException
     */
    public function validate($validations){
        if (isset($this->lead['owner_id'])) {
            parent::validateEmpty($this->lead['owner_id'], 'owner_id');
            $validations['owner'] = $this->checkOwner($this->lead['owner_id']);
        } elseif (isset($this->lead['owner_name'])) {
            $validations['owner'] = $this->checkOwner($this->lead['owner_name']);
            parent::validateEmpty($this->lead['owner_name'], 'owner_name');
        } else {
            throw new LeadValidationException("Lead has no owner.");
        }

        return $validations;
    }

    /**
     * @param $owner_value
     * @throws LeadValidationException
     */
    private function checkOwner($owner_value){
        if (isset($this->lead['owner_name'])){
            return $this->service->getOwnerByName($owner_value, true);
        } elseif (isset($this->lead['owner_id'])) {
            return $this->service->getOwner($owner_value, true);
        } else {
            throw new LeadValidationException("You must specify a owner (it can be an ID or a NAME).");
        }
    }

} 