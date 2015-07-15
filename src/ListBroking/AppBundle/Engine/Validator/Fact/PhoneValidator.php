<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 *
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Validator\Fact;


use Doctrine\ORM\EntityManager;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use ListBroking\AppBundle\Engine\Validator\ValidatorInterface;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Exception\Validation\DimensionValidationException;

class PhoneValidator implements ValidatorInterface {

    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var bool
     */
    protected $is_required;

    protected $rules = array(
        array('regex' => '/(0{5,9}|1{5,9}|2{5,9}|3{5,9}|4{5,9}|5{5,9}|6{5,9}|7{5,9}|8{5,9}|9{5,9})/i', 'msg' => '5 or more equal numbers'),
        array('regex' => '/(0123|1234|2345|3456|4567|5678|6789|7890|0987|9876|8765|7654|6543|5432|4321|3210)/i', 'msg' => '4 sequential number'),
    );

    /**
     * @param EntityManager $em
     * @param bool $is_required
     * @internal param EntityManager $service
     */
    function __construct(EntityManager $em, $is_required){
        $this->entity_manager = $em;
        $this->is_required = $is_required;
    }

    /**
     * Validates the contact against a set of rules
     * @param StagingContact $contact
     * @param $validations
     * @throws DimensionValidationException
     * @return mixed
     */
    public function validate(StagingContact $contact, &$validations)
    {
        $field = strtoupper($contact->getPhone());
        $country = strtoupper($contact->getCountry());
        if(empty($field)){
            if(!$this->is_required){
                return;
            }
            throw new DimensionValidationException('Empty phone field');
        }
        if(empty($country)){
            throw new DimensionValidationException('Empty country field');
        }

        foreach ($this->rules as $rule)
        {
            if(preg_match($rule['regex'], $field)){
                throw new DimensionValidationException("Phone number doesn't match regex rule: {$rule['msg']}");
            }
        }

        $phone_util = PhoneNumberUtil::getInstance();
        try{
            $phone_proto = $phone_util->parse($field, $country);

            // Validate the phone number for the given country
            if(!$phone_util->isValidNumber($phone_proto)){
                throw new DimensionValidationException("Phone number doesn't match with {$country}'s' validation rules");
            }

            // Check if it's a mobile number
            $phone_type = $phone_util->getNumberType($phone_proto);
            switch($phone_type){
                case PhoneNumberType::FIXED_LINE:
                case PhoneNumberType::PERSONAL_NUMBER:
                case PhoneNumberType::FIXED_LINE_OR_MOBILE:
                case PhoneNumberType::VOIP:
                case PhoneNumberType::STANDARD_RATE:
                    $contact->setIsMobile(false);
                    break;
                case PhoneNumberType::MOBILE:
                    $contact->setIsMobile(true);
                    break;
                default:
                    // Use some Reflection trickery to get the constant name
                    $class = new \ReflectionClass(get_class(new PhoneNumberType()));
                    $constants = array_flip($class->getConstants());

                    throw new DimensionValidationException("Phone number is of an invalid type {$constants[$phone_type]}");
                    break;
            }

        }catch (\Exception $e){
            throw new DimensionValidationException("Phone number doesn't match with {$country}'s' validation rules");
        }
    }

    /**
     * Gets the name of the validator
     * @return string
     */
    public function getName(){
        return 'phone_validator';
    }
}