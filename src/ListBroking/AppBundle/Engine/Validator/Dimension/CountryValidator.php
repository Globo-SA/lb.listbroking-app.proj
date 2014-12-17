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
        $field = strtoupper($contact->getCountry());
        if(empty($field)){
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

            $validations[$this->getName()]['warnings'][] = 'New Country created: ' .  $country->getName();
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