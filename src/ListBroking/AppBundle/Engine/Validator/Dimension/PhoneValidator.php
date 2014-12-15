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

namespace ListBroking\AppBundle\Engine\Validator\Dimension;


use Doctrine\ORM\EntityManager;
use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use ListBroking\AppBundle\Engine\Validator\ValidatorInterface;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Exception\Validation\DimensionValidationException;

class PhoneValidator implements ValidatorInterface {

    protected $em;

    /**
     * @param EntityManager $em
     * @internal param EntityManager $service
     */
    function __construct(EntityManager $em){
        $this->em = $em;
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
            throw new DimensionValidationException('Empty phone field');
        }
        if(empty($country)){
            throw new DimensionValidationException('Empty country field');
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
            $validations[$this->getName()]['warnings'][] = $e->getMessage();
        }
    }

    /**
     * Gets the name of the validator
     * @return string
     */
    public function getName(){
        return 'country_validator';
    }
}