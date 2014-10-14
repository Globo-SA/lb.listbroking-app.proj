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
            "phone",
            "email",                // TODO add to advanced configuration
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
        $leads = $request->query->get('lead');

        foreach ($this->fields_to_validate as $key){
            if (!array_key_exists($key, $leads) || empty($leads[$key]))
            {
                throw new APIException("The field ". $key . " cannot be empty.");
            }
        }
    }
}