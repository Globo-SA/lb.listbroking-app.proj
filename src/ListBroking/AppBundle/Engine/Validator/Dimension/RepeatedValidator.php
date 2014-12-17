<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Validator\Dimension;



use Doctrine\ORM\EntityManager;
use ListBroking\AppBundle\Engine\Validator\ValidatorInterface;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Exception\Validation\DimensionValidationException;

class RepeatedValidator implements ValidatorInterface {

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
        $staging_phone = $contact->getPhone();
        $staging_email = $contact->getEmail();
        $staging_owner = $contact->getOwner();

        $lead = $this->em->getRepository('ListBrokingAppBundle:Lead')->findOneBy(array(
            'phone' => $staging_phone
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

            $lead_contact = $this->em->getRepository('ListBrokingAppBundle:Contact')->findOneBy(array(
                'lead' => $lead,
                'email' => $staging_email
            ));

            // Contact_Y associated with Lead_X exists
            if($lead_contact){
                $info .= ' and Phone-Contact';

                $owner_contact = $this->em->getRepository('ListBrokingAppBundle:Contact')->createQueryBuilder('c')
                    ->join('c.owner', 'o')
                    ->where('c = :lead_contact')
                    ->andWhere('o.name = :staging_contact_owner')
                    ->setParameter('lead_contact', $lead_contact)
                    ->setParameter('staging_contact_owner', $staging_owner)
                    ->getQuery()
                    ->getOneOrNullResult()
                ;

                // Owner_Z has Contact_Y associated with Lead_X
                // LEAD IS FULLY REPEATED
                if($owner_contact){
                    throw new DimensionValidationException('Lead is fully repeated');
                }
            }
            $validations[$this->getName()]['info'][] = $info;
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