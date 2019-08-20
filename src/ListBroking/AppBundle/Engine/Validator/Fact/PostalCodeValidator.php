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
use Guzzle\Service\Client;
use ListBroking\AppBundle\Engine\Validator\ValidatorInterface;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Exception\Validation\DimensionValidationException;
use ListBroking\AppBundle\Model\PostalCodeResponse;

class PostalCodeValidator implements ValidatorInterface {

    const POSTALCODE_API_PT_URL = 'https://postalcode.adctools.com/api/postalcode/%s/information?country=%s';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var
     */
    protected $is_required;

    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * @param EntityManager $em
     * @param bool $is_required
     * @param Client $guzzle
     * @throws \Exception
     */
    function __construct(EntityManager $em, $is_required, Client $guzzle = null){
        $this->em = $em;
        $this->is_required = $is_required;
        $this->guzzle = $guzzle;

        if(!$this->guzzle){
            throw new \Exception('Guzzle service needs to be added on the __construct');
        }
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
        $country = strtoupper($contact->getCountry());

        // Clean up merged postal codes
        $explode = explode('-', $contact->getPostalcode1());
        if(count($explode) > 1){
            $contact->setPostalcode1($explode[0]);
            $contact->setPostalcode2($explode[1]);
        }
        $field1 = $contact->getPostalcode1();
        $field2 = $contact->getPostalcode2();

        // Enrich contact PostalCode = District + County + Parish
        if(empty($field1) && empty($field2)){
            if(!$this->is_required){
                return;
            }
            throw new DimensionValidationException('Empty postalcode1 and postalcode2 fields');
        }

        $postalcodeInfo = $this->getPostalCodeDetails($country, $field1, $field2);
        if ($postalcodeInfo == null || $postalcodeInfo->wasSuccessful() === false) {
            return;
        }

        if(!empty($postalcodeInfo['district'])){
            $contact->setDistrict($postalcodeInfo->getDistrictName());
        }
        if(!empty($postalcodeInfo['county'])){
            $contact->setCounty($postalcodeInfo->getCityName());
        }
        if(!empty($postalcodeInfo['parish'])){
            $contact->setParish($postalcodeInfo->getParishName());
        }
    }

    /**
     * Gets the name of the validator
     * @return string
     */
    public function getName()
    {
        return 'postalcode_validator';
    }

    /**
     * @param $country
     * @param $postalcode1
     * @param $postalcode2
     *
     * @return PostalCodeResponse|null
     */
    private function getPostalCodeDetails($country, $postalcode1, $postalcode2)
    {
        switch ($country) {
            case 'PT':
            case 'PL':
                $fullPostalcode = sprintf('%s-%s', $postalcode1, $postalcode2);

                $url = sprintf(self::POSTALCODE_API_PT_URL, $fullPostalcode, $country);

                $request  = $this->guzzle->get($url);
                $response = $request->send();

                return new PostalCodeResponse($response);
            default:
                return null;
        }
    }
}
