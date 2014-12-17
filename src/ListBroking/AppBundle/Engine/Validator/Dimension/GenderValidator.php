<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Validator\Dimension;


use Doctrine\ORM\EntityManager;
use ListBroking\AppBundle\Engine\Validator\ValidatorInterface;
use ListBroking\AppBundle\Entity\Gender;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Exception\Validation\DimensionValidationException;

class GenderValidator implements ValidatorInterface {

    /**
     * @var EntityManager
     */
    protected $em;

    protected $rules = array(
        Gender::MALE => '/^(M|H|MR|MALE|MAN|HOMEM|SR|SENHOR)$/i',
        Gender::FEMALE => '/^(F|MRS|MISS|FEMALE|WOMAN|MULHER|SRA|SENHORA)$/i'
    );

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
        $field = strtoupper($contact->getGender());
        if(empty($field)){
            throw new DimensionValidationException('Empty gender field');
        }

        // Match with a gender
        $gender_matched = false;
        foreach ($this->rules as $gender => $pattern){
            if(preg_match($pattern, $field)){
                $contact->setGender($gender);
                $gender_matched = true;
                break;
            }
        }

        if(!$gender_matched){
            throw new DimensionValidationException('Invalid gender field: ' . $field);
        }
    }

    /**
     * Gets the name of the validator
     * @return string
     */
    public function getName()
    {
        return 'gender_validator';
    }


} 