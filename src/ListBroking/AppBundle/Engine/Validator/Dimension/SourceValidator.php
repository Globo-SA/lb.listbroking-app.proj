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
     *
     * @param StagingContact $contact
     * @param array          $validations
     *
     * @throws DimensionValidationException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function validate(StagingContact $contact, &$validations)
    {
        $externalId = strtoupper($contact->getSourceExternalId());

        if (empty($externalId)) {
            throw new DimensionValidationException('Empty Source external id field');
        }

        /** @var Source $source */
        $source = $this->em->getRepository('ListBrokingAppBundle:Source')
                           ->findOneBy(['external_id' => $externalId]);

        if ($source instanceof Source &&
            strtoupper($source->getOwner()->getName()) !== strtoupper($contact->getOwner())
        ) {
            throw new DimensionValidationException('Owner of Source is not same as the Owner');
        }

        // If Source doesn't exist create it
        if (!$source) {
            $sourceCountry = strtoupper($contact->getSourceCountry());

            if (empty($sourceCountry)) {
                throw new DimensionValidationException('Empty Source.country field');
            }

            $country = $this->em->getRepository('ListBrokingAppBundle:Country')->findOneBy(['name' => $sourceCountry]);
            $owner   = $this->em->getRepository('ListBrokingAppBundle:Owner')->findOneBy(['name' => $contact->getOwner()]);

            if (empty($owner)) {
                throw new DimensionValidationException('Empty owner field');
            }

            // Create new Source Country
            if (!$country) {
                $country = new Country();
                $country->setName($sourceCountry);

                $this->em->persist($country);
                $this->em->flush($country);

                $validations['infos'][$this->getName()][] = 'New Country created: ' .  $country->getName();
            }

            // Create new Source
            $source = new Source();
            $source->setName($contact->getSourceName());
            $source->setCountry($country);
            $source->setExternalId($externalId);
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
    public function getName()
    {
        return 'source_validator';
    }
}
