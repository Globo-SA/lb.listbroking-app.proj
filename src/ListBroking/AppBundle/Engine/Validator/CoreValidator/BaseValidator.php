<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Validator\CoreValidator;


use ListBroking\AppBundle\Engine\Validator\CoreValidatorInterface;
use ListBroking\AppBundle\Exception\CoreValidationException;

class BaseValidator implements CoreValidatorInterface {

    protected $field;

    /**
     * @param $service
     */
    public function __construct($service, $lead)
    {
        $this->service  = $service;
        $this->lead     = $lead;
    }

    /**
     * @param $value
     * @param $field
     * @return bool
     * @throws CoreValidationException
     */
    public function validateEmpty($value, $field)
    {
        if(empty($value)){
            throw new CoreValidationException("Empty field: " . $field . ".\r\n");
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