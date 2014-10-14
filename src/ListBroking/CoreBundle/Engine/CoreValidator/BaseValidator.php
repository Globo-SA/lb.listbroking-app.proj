<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\CoreBundle\Engine\CoreValidator;


use ListBroking\CoreBundle\Engine\CoreValidatorInterface;
use ListBroking\CoreBundle\Exception\CoreValidationException;
use Symfony\Component\HttpFoundation\Request;

class BaseValidator implements CoreValidatorInterface {

    protected $field;

    /**
     * @param $service
     * @param Request $request
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