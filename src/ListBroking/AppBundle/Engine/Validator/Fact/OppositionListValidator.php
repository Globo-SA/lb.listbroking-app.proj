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

class OppositionListValidator implements ValidatorInterface {

    /**
     * @var EntityManager
     */
    private $em;

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
        $in_opposition = $this->em->getRepository('ListBrokingAppBundle:OppositionList')->findOneBy(array(
            'phone' => $contact->getPhone()
        ));
        if($in_opposition)
        {
            $contact->setInOpposition(true);
            $contact->setValid(false);

            throw new DimensionValidationException('Lead is the opposition list: ' . $in_opposition->getType());
        }
    }

    /**
     * Gets the name of the validator
     * @return string
     */
    public function getName()
    {
        return 'opposition_list_validator';
    }


}