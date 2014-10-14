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


use ListBroking\LeadBundle\Exception\LeadValidationException;
use Symfony\Component\HttpFoundation\Request;

class OwnerValidator extends BaseValidator {
    public function __construct($service, Request $request)
    {
        parent::__construct($service, $request);
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

        if ($owner == null){
            throw new LeadValidationException("This owner_id/name does not exist. Please enter a valid id/name for the owner.");
        }
    }

} 