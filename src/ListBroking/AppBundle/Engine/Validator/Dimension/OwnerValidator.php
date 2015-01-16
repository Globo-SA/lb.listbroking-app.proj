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
use ListBroking\AppBundle\Engine\Validator\ValidatorInterface;
use ListBroking\AppBundle\Entity\Owner;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Exception\Validation\DimensionValidationException;

class OwnerValidator implements ValidatorInterface {

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
    public function validate(StagingContact $contact, &$validations)
    {
        $field = strtoupper($contact->getOwner());
        if(empty($field)){
            if(!$this->is_required){
                return;
            }
            throw new DimensionValidationException('Empty owner field');
        }

        $owner =  $this->em->getRepository('ListBrokingAppBundle:Owner')->findOneBy(array(
                'name' => $field
            ));

        // If doesn't exist create it
        if(!$owner){
            $owner = new Owner();
            $owner->setName($field);

            $this->em->persist($owner);

            $validations['infos'][$this->getName()][] = 'New Owner created: ' .  $owner->getName();
        }
    }

    /**
     * Gets the name of the validator
     * @return string
     */
    public function getName(){
        return 'owner_validator';
    }
}