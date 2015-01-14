<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine;


use Doctrine\ORM\EntityManager;
use Guzzle\Service\Client;
use ListBroking\AppBundle\Engine\Validator\Dimension\CountryValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\CategoryValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\OwnerValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\SourceValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\GenderValidator;

use ListBroking\AppBundle\Engine\Validator\Fact\EmailValidator;
use ListBroking\AppBundle\Engine\Validator\Fact\PhoneValidator;
use ListBroking\AppBundle\Engine\Validator\Fact\RepeatedValidator;
use ListBroking\AppBundle\Engine\Validator\Fact\OppositionListValidator;
use ListBroking\AppBundle\Engine\Validator\Fact\PostalCodeValidator;

use ListBroking\AppBundle\Engine\Validator\ValidatorInterface;
use ListBroking\AppBundle\Entity\StagingContact;

class ValidatorEngine
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * @var ValidatorInterface[]
     */
    protected $validators;

    function __construct(EntityManager $em, Client $guzzle)
    {
        $this->em = $em;
        $this->guzzle = $guzzle;

        //TODO: For now all validations are iterated even if the contact is invalided by one
        $this->validators = array(
            // Dimension validations
            new CountryValidator($this->em),
            new CategoryValidator($this->em),
            new OwnerValidator($this->em),
            new SourceValidator($this->em),
            new GenderValidator($this->em), // Dimension with fixed values

            // Fact validations
            new EmailValidator($this->em),
            new PhoneValidator($this->em),
            new RepeatedValidator($this->em),
            new OppositionListValidator($this->em),
            new PostalCodeValidator($this->em, $this->guzzle),
        );
    }

    /**
     * Runs Validators to an array of
     * StagingContacts
     * @param $contact StagingContact
     */
    public function run($contact)
    {
        $validations = $contact->getValidations();
        foreach ($this->validators as $validator)
        {
            try
            {
                $validator->validate($contact, $validations);
            } catch (\Exception $e)
            {
                $validations['errors'][$validator->getName()][] = $e->getMessage();
            }
        }
        $contact->setValidations($validations);
    }
}