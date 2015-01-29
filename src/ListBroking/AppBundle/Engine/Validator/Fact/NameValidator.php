<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Validator\Fact;


use Doctrine\ORM\EntityManager;
use ListBroking\AppBundle\Engine\Validator\ValidatorInterface;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Exception\Validation\DimensionValidationException;

class NameValidator implements ValidatorInterface {

    protected $rules = array(
        array('regex' => '/^\S{1}$/i', 'msg' => 'Name only contains one letter'),
        array('regex' => '/\d/i', 'msg' => 'Name contains a digit'),
        array('regex' => '/(test|teste|fake|asd)/i', 'msg' => 'Test name'),
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
     * @internal param EntityManager $service
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
    public function validate(StagingContact $contact, &$validations){

        $firstname = $contact->getFirstname();
        $lastname = $contact->getLastname();
        if(empty($firstname)){
            if(!$this->is_required){
                return;
            }
            throw new DimensionValidationException('Empty firstname field');
        }

        if(empty($lastname)){
            $name = explode(' ', $firstname, 2);

            $firstname = $name[0];
            $contact->setFirstname($firstname);
            if(count($name) > 1){
                $lastname = $name[1];
                $contact->setLastname($lastname);
            }
        }

        foreach ($this->rules as $rule)
        {
            if(preg_match($rule['regex'], $firstname)){
                throw new DimensionValidationException("Firstname matched a invalid regex rule: {$rule['msg']}");
            }
            if(preg_match($rule['regex'], $lastname)){
                throw new DimensionValidationException("Lastname  matched a invalid regex rule: {$rule['msg']}");
            }
        }

        // Uppercase the first character of each word in a string
        $contact->setFirstname(ucwords($firstname));
        $contact->setLastname(ucwords($lastname));
    }

    /**
     * Gets the name of the validator
     * @return string
     */
    public function getName()
    {
        return 'name_validator';
    }


} 