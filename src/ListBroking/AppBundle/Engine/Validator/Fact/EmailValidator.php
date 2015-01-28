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

class EmailValidator implements ValidatorInterface {

    protected $rules = array(
        array('regex' => '/(adctst|adclick|test)/i', 'msg' => 'Test contact'),
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
        $this->is_required = $is_required; // Doesn't use it
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
        $field = strtoupper($contact->getEmail());

        // If the email doesn't exist generate a fake one
        if(empty($field)){
            $contact->setEmail($contact->getPhone() . '_' . $contact->getOwner() . "@listbroking.adctools.com");
            $validations['infos'][$this->getName()][] = 'Fake email generated: ' .  $contact->getEmail();

            return;
        }

        foreach ($this->rules as $rule)
        {
            if(preg_match($rule['regex'], $field)){
                throw new DimensionValidationException("Email address matched a invalid regex rule: {$rule['msg']}");
            }
        }
    }

    /**
     * Gets the name of the validator
     * @return string
     */
    public function getName()
    {
        return 'email_validator';
    }


} 