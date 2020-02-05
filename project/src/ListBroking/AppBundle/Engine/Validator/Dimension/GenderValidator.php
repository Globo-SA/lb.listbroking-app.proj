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

    protected $rules = array(
        Gender::MALE => '/^(M|H|MR|MALE|MAN|HOMEM|SR|SENHOR|MENINO)$/i',
        Gender::FEMALE => '/^(F|MRS|MISS|FEMALE|WOMAN|MULHER|SRA|SENHORA|MENINA)$/i',
        Gender::EMPTY_FIELD => '/(N\/A|\s|ON)/i'
    );

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var bool
     */
    protected $is_required;

    /**
     * @param EntityManager $em
     * @param bool $is_required
     */
    function __construct(EntityManager $em, $is_required){
        $this->em = $em;
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
        $field = strtoupper($contact->getGender());
        if(empty($field)){
            if(!$this->is_required){
                return;
            }
            $field = Gender::EMPTY_FIELD;
        }

        // Match with a gender
        $gender_matched = false;
        foreach ($this->rules as $gender => $pattern){
            if(preg_match($pattern, $field)){
                $contact->setGender($gender);
                $field = $gender;
                $gender_matched = true;
                break;
            }
        }

        if(!$gender_matched){
            $field = Gender::EMPTY_FIELD;
            $contact->setGender($field);
        }

        $gender =  $this->em->getRepository('ListBrokingAppBundle:Gender')->findOneBy(array(
            'name' => $field
        ));

        // If doesn't exist create it
        if(!$gender){
            $gender = new Gender();
            $gender->setName($field);

            $this->em->persist($gender);
            $this->em->flush($gender);

            $validations['infos'][$this->getName()][] = 'New Gender created: ' .  $gender->getName();
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