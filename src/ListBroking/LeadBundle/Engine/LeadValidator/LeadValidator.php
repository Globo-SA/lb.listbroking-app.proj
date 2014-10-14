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

class LeadValidator extends BaseValidator {
    protected $service;
    protected $request;

    /**
     * @param $service
     */
    public function __construct($service, Request $request)
    {
        parent::__construct($service, $request);
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
        if ($validations[''] == ) {
            $validations['is_mobile']     = $this->checkMobilePhone($phone, $this->lead['country']);
        }
        $validations['repeated_lead'] = $this->checkLeadExistance($phone);
//        ladybug_dump_die($validations);
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
            return true;
        }
        return false;
    }

    /**
     * @param $phone
     * @return mixed
     */
    private function checkLeadExistance($phone){
        return $this->service->getLeadByPhone($phone, true);
    }
}