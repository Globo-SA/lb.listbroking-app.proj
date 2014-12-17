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


use Guzzle\Service\Client;
use ListBroking\AppBundle\Engine\Validator\Dimension\CategoryValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\CountryValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\GenderValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\OwnerValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\PhoneValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\PostalCodeValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\RepeatedValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\SourceValidator;
use ListBroking\AppBundle\Engine\Validator\ValidatorInterface;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Service\Base\BaseService;

class ValidatorEngine extends BaseService
{
    protected $guzzle;

    /**
     * @var ValidatorInterface[]
     */
    protected $validators;

    function __construct(Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * Runs Validators to an array of
     * StagingContacts
     * @param $contacts StagingContact[]
     */
    public function run($contacts)
    {
        $this->setValidators();
        foreach ($contacts as $contact)
        {
            $validations = $contact->getValidations();
            foreach ($this->validators as $validator)
            {
                try
                {
                    $validator->validate($contact, $validations);
                } catch (\Exception $e)
                {
                    $validations[$validator->getName()]['errors'][] = $e->getMessage();
                }
            }
            $contact->setValidations($validations);

            // Flush all changes
            $this->em->flush();
        }

    }

    private function setValidators()
    {
        //TODO: Add cache to Dimension Validations
        $this->validators = array(
            // Dimension validations
            new CountryValidator($this->em),
            new CategoryValidator($this->em),
            new OwnerValidator($this->em),
            new SourceValidator($this->em),

            // Fact validations
            new PhoneValidator($this->em),
            new RepeatedValidator($this->em),
            new GenderValidator($this->em),

            new PostalCodeValidator($this->em, $this->guzzle),
        );
    }


}