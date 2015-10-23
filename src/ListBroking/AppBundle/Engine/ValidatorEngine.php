<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Guzzle\Service\Client;
use ListBroking\AppBundle\Engine\Validator\Dimension\CategoryValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\CountryValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\CountyValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\DistrictValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\GenderValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\OwnerValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\ParishValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\SourceValidator;
use ListBroking\AppBundle\Engine\Validator\Fact\AddressValidator;
use ListBroking\AppBundle\Engine\Validator\Fact\BirthdateValidator;
use ListBroking\AppBundle\Engine\Validator\Fact\EmailValidator;
use ListBroking\AppBundle\Engine\Validator\Fact\NameValidator;
use ListBroking\AppBundle\Engine\Validator\Fact\OppositionListValidator;
use ListBroking\AppBundle\Engine\Validator\Fact\PhoneValidator;
use ListBroking\AppBundle\Engine\Validator\Fact\PostalCodeValidator;
use ListBroking\AppBundle\Engine\Validator\Fact\RepeatedValidator;
use ListBroking\AppBundle\Engine\Validator\ValidatorInterface;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Service\Helper\AppServiceInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ValidatorEngine
{

    /**
     * @var Registry
     */
    protected $doctrine;

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

    /**
     * @var AppServiceInterface
     */
    protected $a_service;

    function __construct (RegistryInterface $doctrine, Client $guzzle, AppServiceInterface $a_service)
    {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
        $this->guzzle = $guzzle;
        $this->a_service = $a_service;

        $this->validators = array(

            // Dimension validations
            new CountryValidator($this->em, true),
            new PostalCodeValidator($this->em, false, $this->guzzle),
            new AddressValidator($this->em, false),
            new DistrictValidator($this->em, false),
            new CountyValidator($this->em, false),
            new ParishValidator($this->em, false),
            new CategoryValidator($this->em, true),
            new OwnerValidator($this->em, true),
            new SourceValidator($this->em, true),
            new GenderValidator($this->em, true), // Dimension with fixed values

            // Fact validations
            new EmailValidator($this->em, true),
            new PhoneValidator($this->em, true),
            new OppositionListValidator($this->em, true),
            new NameValidator($this->em, true),
            new BirthdateValidator($this->em, false),
            new RepeatedValidator($this->em, true)
        );
    }

    /**
     * Runs Validators to an array of
     * StagingContacts
     *
     * @param $contact StagingContact
     */
    public function run ($contact)
    {
        $validations = array();
        foreach ( $this->validators as $validator )
        {
            try
            {
                // Validate
                $this->a_service->startStopWatch('validator_engine');
                $validator->validate($contact, $validations);
                $this->a_service->logInfo(sprintf('Stopwatch: validator %s, ran in %s milliseconds', $validator->getName(), $this->a_service->lapStopWatch('validator_engine')));
            }
            catch ( \Exception $e )
            {
                $validations['errors'][$validator->getName()][] = $e->getMessage() . ', line:' . $e->getLine();
            }
        }

        $contact->setProcessed(true);
        $contact->setValidations($validations);
        if ( ! array_key_exists('errors', $validations) )
        {
            $contact->setValid(true);
        }

        try
        {
            $this->em->flush();
        }
        catch ( \Exception $e )
        {
            $validations = array('exceptions' => $e->getMessage());
            $contact->setValidations($validations);
            $contact->setValid(false);

            // Resets the Manager and rollsback all the entities
            $this->doctrine->resetManager();
            $this->em = $this->doctrine->getManager();
            $this->em->merge($contact);
            $this->em->flush();
        }
    }
}