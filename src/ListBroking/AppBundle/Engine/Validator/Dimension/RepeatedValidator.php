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
        $phone = $contact->getPhone();
        $email = $contact->getEmail();

        $lead = $this->em->getRepository('ListBrokingAppBundle:Lead')->findOneBy(array(
            'phone' => $phone
        ));

        // Lead_X exists
        if($lead){
            $validations[$this->getName()]['warnings'][] = 'Lead already exists';

            $contact = $this->em->getRepository('ListBrokingAppBundle:Contact')->findOneBy(array(
                'lead' => $lead,
                'email' => $email
            ));

            // Contact_Y associated with Lead_X exists
            if($contact){
                $validations[$this->getName()]['warnings'][] = 'Contact associated with Lead already exists';

                $owner_contact = $this->em->getRepository('ListBrokingAppBundle:Contact')->createQueryBuilder('c')
                    ->join('c.owner', 'owner')
                    ->andWhere('c', $contact)
                    ->andWhere('owner.name', $contact->getOwner())
                ;

                // Owner_Z has Contact_Y associated with Lead_X
                // LEAD IS FULLY REPEATED
                if($owner_contact){
                    throw new DimensionValidationException('Lead is fully repeated');

                }
            }
        }
    }

    /**
     * Gets the name of the validator
     * @return string
     */
    public function getName()
    {
        // TODO: Implement getName() method.
    }


} 