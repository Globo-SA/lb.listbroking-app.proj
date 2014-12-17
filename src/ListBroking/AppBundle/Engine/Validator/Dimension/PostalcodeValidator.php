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
use Guzzle\Service\Client;
use ListBroking\AppBundle\Engine\Validator\ValidatorInterface;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Exception\Validation\DimensionValidationException;

class PostalCodeValidator implements ValidatorInterface {

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Client
     */
    protected $guzzle;

    const POSTALCODE_API_URL = 'http://postalcode.adctools.com';

    /**
     * @param EntityManager $em
     * @param Client $guzzle
     * @throws \Exception
     * @internal param EntityManager $service
     */
    function __construct(EntityManager $em, Client $guzzle = null){
        $this->em = $em;
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
        $field1 = strtoupper($contact->getPostalcode1());
        $field2 = strtoupper($contact->getPostalcode2());

        // Enrich contact PostalCode = District + County + Parish
        $postalcode_info = $this->getPostalCodeDetails($country, $field1, $field2);
        if(empty($field1) && empty($field2)){
            throw new DimensionValidationException('Empty postalcode1 and postalcode2 fields');
        }

        if($postalcode_info){
            if(!empty($postalcode_info['district'])){
                $contact->setDistrict($postalcode_info['district']);
            }
            if(!empty($postalcode_info['county'])){
                $contact->setCounty($postalcode_info['county']);
            }
            if(!empty($postalcode_info['parish'])){
                $contact->setParish($postalcode_info['parish']);
            }
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

    private function getPostalCodeDetails($country, $postalcode1, $postalcode2){

        $info = null;
        switch($country){
            case 'PT':
                $request = $this->guzzle->get(PostalCodeValidator::POSTALCODE_API_URL, null, array('query'=> array(
                    'postal' => array('cp1' => $postalcode1, 'cp2' =>  $postalcode2)
                )));
                $response =  $request->send();
                $results = json_decode($response->getBody(true), true);
                if($results['code'] == 200){

                    // Mapping from the API
                    $info['district'] = $results['result'][0]['distrito'];
                    $info['county'] = $results['result'][0]['localidade'];
                    $info['parish'] = $results['result'][0]['localidade'];
                }
                break;
            default:
                break;
        }

        return $info;
    }


} 