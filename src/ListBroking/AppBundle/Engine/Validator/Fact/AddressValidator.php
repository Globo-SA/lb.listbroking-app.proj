<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Engine\Validator\Fact;

use Doctrine\ORM\EntityManager;
use ListBroking\AppBundle\Engine\Validator\ValidatorInterface;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Exception\Validation\DimensionValidationException;

class AddressValidator implements ValidatorInterface
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var
     */
    protected $is_required;

    /**
     * @param EntityManager $em
     * @param bool          $is_required
     *
     * @throws \Exception
     */
    function __construct (EntityManager $em, $is_required)
    {
        $this->em = $em;
        $this->is_required = $is_required;
    }

    /**
     * Validates the contact against a set of rules
     *
     * @param StagingContact $contact
     * @param                $validations
     *
     * @throws DimensionValidationException
     * @return mixed
     */
    public function validate (StagingContact $contact, &$validations)
    {
        $address = $contact->getAddress();
        if ( empty($address) )
        {
            if ( ! $this->is_required )
            {
                return;
            }
            throw new DimensionValidationException('Empty address field');
        }

        $contact->setAddress(strtoupper($address));
    }

    /**
     * Gets the name of the validator
     * @return string
     */
    public function getName ()
    {
        return 'address_validator';
    }
}