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

class BirthdateValidator implements ValidatorInterface {

    const MAX_AGE = 90;

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
        $birthdate = trim($contact->getBirthdate());
        if(empty($birthdate)){
            if(!$this->is_required){
                return;
            }
            throw new DimensionValidationException('Empty birthdate field');
        }
        $now = new \DateTime();
        $birthdate = new \DateTime($birthdate);

        $age = $now->diff($birthdate)->y;
        if($age > self::MAX_AGE){
            throw new DimensionValidationException('Birthdate it greater than: ' . self::MAX_AGE . " (current: {$age})");
        }
    }

    /**
     * Gets the name of the validator
     * @return string
     */
    public function getName()
    {
        return 'birthdate_validator';
    }


} 