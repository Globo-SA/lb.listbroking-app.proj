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


use ListBroking\LeadBundle\Engine\LeadValidatorInterface;
use ListBroking\LeadBundle\Exception\LeadValidationException;
use Symfony\Component\HttpFoundation\Request;

class BaseValidator implements LeadValidatorInterface {

    protected $field;

    /**
     * @param $service
     * @param $request
     */
    public function __construct($service, Request $request)
    {
        $this->service = $service;
        $this->lead = $request->query->get('lead');
    }

    /**
     * @param $value
     * @param $field
     * @return bool
     * @throws LeadValidationException
     */
    public function validateEmpty($value, $field)
    {
        if(empty($value)){
            throw new LeadValidationException("Empty field: " . $field . ".\r\n");
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

} 