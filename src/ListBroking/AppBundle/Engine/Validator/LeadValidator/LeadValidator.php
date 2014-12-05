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

class LeadValidator extends BaseValidator {
    protected $service;
    protected $request;

    /**
     * @param $service
     * @param $lead
     */
    public function __construct($service, $lead)
    {
        parent::__construct($service, $lead);
        $this->mobile_prefixs = array(
            'PT' => '91|92|93|96'
        );
    }

    /**
     * @param $validations
     * @return mixed
     * @throws LeadValidationException
     */
    public function validate($validations)
    {
        if (!isset($this->lead['phone'])){
            throw new LeadValidationException("Field lead[phone] not sent. \n");
        }
        $this->validatePhone($this->lead['phone']);
        $phone = $this->lead['phone'];
        $validations['phone']         = $phone;

        if ($validations['country']->getIsoCode() == 'PT') {
            $validations['is_mobile']     = $this->checkMobilePhone($phone, $validations['country']->getIsoCode());
        }
        $validations['repeated_lead'] = $this->checkLeadExistence($phone);
        return $validations;
    }

    /**
     * @param $phone
     * @return mixed
     * @throws LeadValidationException
     */
    private function validatePhone($phone){
        parent::validateEmpty($phone, 'phone');
        return $phone;
    }

    /**
     * @param $phone
     * @param $country
     * @return bool
     */
    private function checkMobilePhone($phone, $country){
        if (preg_match('/' . $this->mobile_prefixs[$country] . '/', $phone)){
            return 1;
        }
        return 0;
    }

    /**
     * @param $phone
     * @return mixed
     */
    private function checkLeadExistence($phone){
        return $this->service->getLeadByPhone($phone, true);
    }
}