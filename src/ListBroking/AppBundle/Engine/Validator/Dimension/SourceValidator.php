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
        $field = strtoupper($contact->getSourceName());
        if(empty($field)){
            if(!$this->is_required){
                return;
            }
            throw new DimensionValidationException('Empty Source field');
        }

        /** @var Source $source */
        $source = $this->em->getRepository('ListBrokingAppBundle:Source')->findOneBy(array(
            'name' => $field
        ));

        if ($source->getOwner()->getName() !== $contact->getOwner()) {
            throw new DimensionValidationException('Owner of Source is not same as the Owner');
        }

        // If Source doesn't exist create it
        if(!$source){

            $source_country = strtoupper($contact->getSourceCountry());
            if(empty($source_country)){
                throw new DimensionValidationException('Empty Source.country field');
            }

            $country = $this->em->getRepository('ListBrokingAppBundle:Country')->findOneBy(array(
                    'name' => $source_country
            ));

            $owner = $this->em->getRepository('ListBrokingAppBundle:Owner')->findOneBy(array(
                'name' => $contact->getOwner()
                )
            );
            if(empty($owner)){
                throw new DimensionValidationException('Empty owner field');
            }

            // Create new Source Country
            if(!$country){
                $country = new Country();
                $country->setName($source_country);

                $this->em->persist($country);
                $this->em->flush($country);

                $validations['infos'][$this->getName()][] = 'New Country created: ' .  $country->getName();
            }

            // Create new Source
            $source = new Source();
            $source->setName($field);
            $source->setCountry($country);
            $source->setExternalId($contact->getSourceExternalId());
            $source->setOwner($owner);

            $this->em->persist($source);
            $this->em->flush($source);

            $validations['infos'][$this->getName()][] = 'New Source created: ' .  $source->getName();
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
