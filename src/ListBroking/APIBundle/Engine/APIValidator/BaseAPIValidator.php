<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\APIBundle\Engine\APIValidator;


use ListBroking\APIBundle\Engine\APIServiceLeadValidatorInterface;
use ListBroking\APIBundle\Exception\APIException;
use Symfony\Component\HttpFoundation\Request;

class BaseAPIValidator implements APIServiceLeadValidatorInterface {
    protected $fields_to_validate;

    /**
     *  Only intializes $fields_to_validate with advanced configuration values
     */
    public function __construct()
    {
        $this->fields_to_validate = array(
            "email",                // TODO add to advanced configuration
            "phone",
            "ipaddress",
        );
    }

    /**
     * @param Request $request
     * @return mixed|void
     * @throws APIException
     */
    public function checkEmptyFields(Request $request)
    {
        $lead = $request->get('lead');
        if (!isset($lead) || !is_array($lead)){
            throw new APIException("The leads array cannot be empty and must be an array.");
        }
        foreach ($this->fields_to_validate as $key){
            if (!isset($lead[$key]) || !array_key_exists($key, $lead) || empty($lead[$key]))
            {
                throw new APIException("The field ". $key . " cannot be empty.");
            }
        }
    }
}