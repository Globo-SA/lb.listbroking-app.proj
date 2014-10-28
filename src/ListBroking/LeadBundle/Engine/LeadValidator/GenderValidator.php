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

class GenderValidator extends BaseValidator {
    /**
     * @param $service
     * @param Request $request
     */
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

        if (!isset($this->lead['gender'])){
            throw new LeadValidationException("Gender not sent. " . var_dump($this->lead));
        }

        parent::validateEmpty($this->lead['gender'], 'gender');
        $validations['gender'] = $this->parameterizeGender($this->lead['gender']);

        $validations['gender']  = $this->service->getGender($validations['gender'], true);

        return $validations;
    }

    /**
     * @param $value
     * @throws LeadValidationException
     */
    private function parameterizeGender($value){
        $value = strtoupper($value);
        $male_array = array(
            'M',
            'MR',
            'MALE',
            'MAN'
        );
        $female_array = array(
            'F',
            'MISS',
            'MRS',
            'FEMALE',
            'WOMAN'
        );
        if (in_array($value, $male_array)){
            return 1;
        } elseif (in_array($value, $female_array)) {
            return 2;
        } else {
            throw new LeadValidationException("Gender format not correct.\n");
        }
    }
} 