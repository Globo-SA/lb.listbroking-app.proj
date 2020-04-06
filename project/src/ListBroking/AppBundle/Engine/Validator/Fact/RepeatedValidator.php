<?php

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
     *
     * @param StagingContact $contact
     * @param                $validations
     *
     * @return mixed
     * @throws DimensionValidationException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function validate(StagingContact $contact, &$validations)
    {
        $staging_phone = $contact->getPhone();
        $staging_email = $contact->getEmail();
        $staging_owner = strtoupper($contact->getOwner());
        $staging_source_external_id = $contact->getSourceExternalId();
        $staging_date = $contact->getDate();

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
         *  . and email and phone combination already exist for the given owner, source and date
         */

        // Lead_X exists
        if($lead){
            $info = 'Lead Repeated by: Phone';
            $contact->setLeadId($lead->getId());

            /** @var Contact $owner_contact */
            $owner_contact = $this->em->getRepository('ListBrokingAppBundle:Contact')->createQueryBuilder('c')
                ->join('c.owner', 'o')
                ->join('c.source', 's')
                ->where('c.lead = :lead')
                ->andWhere('c.email = :staging_email')
                ->andWhere('o.name = :staging_contact_owner')
                ->andWhere('s.external_id = :staging_contact_external_source')
                ->andWhere('c.date >= :staging_contact_date')
                ->setParameter('lead', $lead)
                ->setParameter('staging_email', $staging_email)
                ->setParameter('staging_contact_owner', $staging_owner)
                ->setParameter('staging_contact_external_source', $staging_source_external_id)
                ->setParameter('staging_contact_date', $staging_date)
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
