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
use ListBroking\AppBundle\Entity\Country;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Exception\Validation\DimensionValidationException;

class CountryValidator implements ValidatorInterface {

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
        $field = strtoupper($contact->getCountry());
        if(empty($field)){
            if(!$this->is_required){
                return;
            }
            throw new DimensionValidationException('Empty country field');
        }
        if(strlen($field) != 2){
            throw new DimensionValidationException('Country field should be an ISO code');
        }

        $country =  $this->em->getRepository('ListBrokingAppBundle:Country')->findOneBy(array(
                'name' => $field
            ));

        // If doesn't exist create it
        if(!$country){
            $country = new Country();
            $country->setName($field);

            $this->em->persist($country);

            $validations['infos'][$this->getName()][] = 'New Country created: ' .  $country->getName();
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