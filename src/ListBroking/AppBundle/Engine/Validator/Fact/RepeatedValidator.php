<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Validator\Fact;



use Doctrine\ORM\EntityManager;
use ListBroking\AppBundle\Engine\Validator\ValidatorInterface;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Exception\Validation\DimensionValidationException;

class RepeatedValidator implements ValidatorInterface {

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
        $staging_phone = $contact->getPhone();
        $staging_email = $contact->getEmail();
        $staging_owner = strtoupper($contact->getOwner());

        $country = $this->em->getRepository('ListBrokingAppBundle:Country')->findOneBy(array(
                'name' => $contact->getCountry()
            )
        );
        $lead = $this->em->getRepository('ListBrokingAppBundle:Lead')->findOneBy(array(
            'phone' => $staging_phone,
            'country' => $country
        ));

        /**
         * A Lead is only FULLY REPEATED if:
         *  . phone number exists
         *  . and email address exists associated with the phone
         *  . and email and phone combination already exist for the given owner
         */

        // Lead_X exists
        if($lead){
            $info = 'Lead Repeated by: Phone';
            $contact->setLeadId($lead->getId());

            /** @var Contact $owner_contact */
            $owner_contact = $this->em->getRepository('ListBrokingAppBundle:Contact')->createQueryBuilder('c')
                ->join('c.owner', 'o')
                ->where('c.lead = :lead')
                ->andWhere('c.email = :staging_email')
                ->andWhere('o.name = :staging_contact_owner')
                ->setParameter('lead', $lead)
                ->setParameter('staging_email', $staging_email)
                ->setParameter('staging_contact_owner', $staging_owner)
                ->getQuery()
                ->getOneOrNullResult()
            ;

            // Owner_Z has Contact_Y associated with Lead_X
            // LEAD IS FULLY REPEATED
            if($owner_contact){
                $contact->setContactId($owner_contact->getId());
                $info = 'Lead is fully repeated';
            }
            $validations['infos'][$this->getName()][] = $info;

            if($owner_contact && $owner_contact->isClean())
            {
                throw new DimensionValidationException('Contact already exists and cleaned');
            }
        }
    }

    /**
     * Gets the name of the validator
     * @return string
     */
    public function getName()
    {
        return 'repeated_validator';
    }


} 