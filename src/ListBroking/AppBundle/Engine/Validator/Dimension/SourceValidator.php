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
use ListBroking\AppBundle\Entity\Source;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Exception\Validation\DimensionValidationException;

class SourceValidator implements ValidatorInterface {

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
        $field = strtoupper($contact->getSourceName());
        if(empty($field)){
            throw new DimensionValidationException('Empty Source field');
        }

        $source = $this->em->getRepository('ListBrokingAppBundle:Source')->findOneBy(array(
            'name' => $field
        ));

        // If Source doesn't exist create it
        if(!$source){

            $source_country = strtoupper($contact->getSourceCountry());
            if(empty($source_country)){
                throw new DimensionValidationException('Empty Source.country field');
            }

            $country = $this->em->getRepository('ListBrokingAppBundle:Country')->findOneBy(array(
                    'name' => $source_country
            ));

            // Create new Source Country
            if(!$country){
                $country = new Country();
                $country->setName($source_country);

                $this->em->persist($country);

                $validations['warnings'][$this->getName()][] = 'New Country created: ' .  $country->getName();
            }

            // Create new Source
            $source = new Source();
            $source->setName($field);
            $source->setCountry($country);
            $source->setExternalId($contact->getSourceExternalId());

            $this->em->persist($source);

            $validations['warnings'][$this->getName()][] = 'New Source created: ' .  $country->getName();
        }
    }

    /**
     * Gets the name of the validator
     * @return string
     */
    public function getName(){
        return 'source_validator';
    }

}