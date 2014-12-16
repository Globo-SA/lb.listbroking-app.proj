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


use ListBroking\AppBundle\Engine\Validator\Dimension\CategoryValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\CountryValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\OwnerValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\PhoneValidator;
use ListBroking\AppBundle\Engine\Validator\Dimension\SourceValidator;
use ListBroking\AppBundle\Engine\Validator\DimensionValidatorInterface;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Service\Base\BaseService;

class ValidatorEngine extends BaseService
{
    /**
     * @var DimensionValidatorInterface[]
     */
    protected $validators;

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
        $this->validators = array(
            // Dimension validations
            new CountryValidator($this->em),
            new CategoryValidator($this->em),
            new OwnerValidator($this->em),
            new SourceValidator($this->em),

            // Fact validations
            new PhoneValidator($this->em),
//            // ESSENTIAL VALIDATIONS
//            new ContactValidator($this->lead_service, $this->lead),
//            new SourceValidator($this->contact_detail_service, $this->lead),
//            // NON-ESSENTIAL
//            new CountyValidator($this->contact_detail_service, $this->lead),
//            new DistrictValidator($this->contact_detail_service, $this->lead),
//            new GenderValidator($this->contact_detail_service, $this->lead),
//            new ParishValidator($this->contact_detail_service, $this->lead),
//            new CountyValidator($this->contact_detail_service, $this->lead)
        );
    }


}