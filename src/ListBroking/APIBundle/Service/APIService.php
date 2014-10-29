<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\APIBundle\Service;



use ListBroking\APIBundle\Engine\APIValidator\BaseAPIValidator;
use ListBroking\APIBundle\Entity\APIToken;
use ListBroking\APIBundle\Exception\APIException;
use ListBroking\APIBundle\Repository\ORM\APITokenRepository;
use ListBroking\CoreBundle\Engine\CoreValidator\CategoryValidator;
use ListBroking\CoreBundle\Engine\CoreValidator\CountryValidator;
use ListBroking\CoreBundle\Exception\CoreValidationException;
use ListBroking\CoreBundle\Service\BaseService;
use ListBroking\CoreBundle\Service\CoreService;
use ListBroking\LeadBundle\Engine\LeadValidator\ContactValidator;
use ListBroking\LeadBundle\Engine\LeadValidator\CountyValidator;
use ListBroking\LeadBundle\Engine\LeadValidator\DistrictValidator;
use ListBroking\LeadBundle\Engine\LeadValidator\GenderValidator;
use ListBroking\LeadBundle\Engine\LeadValidator\LeadValidator;
use ListBroking\LeadBundle\Engine\LeadValidator\OwnerValidator;
use ListBroking\LeadBundle\Engine\LeadValidator\ParishValidator;
use ListBroking\LeadBundle\Engine\LeadValidator\SourceValidator;
use ListBroking\LeadBundle\Entity\Contact;
use ListBroking\LeadBundle\Entity\Lead;
use ListBroking\LeadBundle\Exception\LeadValidationException;
use ListBroking\LeadBundle\Service\ContactDetailsService;
use ListBroking\LeadBundle\Service\LeadService;
use ListBroking\LockBundle\Entity\Lock;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class APIService extends BaseService implements APIServiceInterface {

    protected $lead_service;
    protected $core_service;
    protected $form_factory;
    protected $contact_detail_service;
    protected $request;
    protected $validation_service;
    protected $validators;

    protected $api_token_repo;

    protected $lead_country;
    const API_LIST = 'apitoken_list';
    const API_SCOPE = 'apitoken';

    /**
     * @param LeadService $leadService
     * @param CoreService $coreService
     * @param ContactDetailsService $contactDetailsService
     * @param RequestStack $requestStack
     * @param APITokenRepository $tokenRepository
     */
    public function __construct(LeadService $leadService, CoreService $coreService, ContactDetailsService $contactDetailsService, RequestStack $requestStack, APITokenRepository $tokenRepository)
    {
        $this->api_token_repo = $tokenRepository;
        $this->lead_service = $leadService;
        $this->core_service = $coreService;
        $this->contact_detail_service = $contactDetailsService;
        $this->validation_service = new BaseAPIValidator($this->core_service, $this->lead_service);
        $this->request = $requestStack->getCurrentRequest();
        $this->validators = array(
            // ESSENTIAL VALIDATIONS
            new CountryValidator($this->core_service, $this->request),
            new CategoryValidator($this->core_service, $this->request),
            new LeadValidator($this->lead_service, $this->request),
            new OwnerValidator($this->contact_detail_service, $this->request),
            new ContactValidator($this->lead_service, $this->request),
            new SourceValidator($this->contact_detail_service, $this->request),
            // NON-ESSENTIAL
            new CountyValidator($this->contact_detail_service, $this->request),
            new DistrictValidator($this->contact_detail_service, $this->request),
            new GenderValidator($this->contact_detail_service, $this->request),
            new ParishValidator($this->contact_detail_service, $this->request),
            new CountyValidator($this->contact_detail_service, $this->request)
        );
        $this->validations = array();
    }

    /**
     * @return JsonResponse
     * @throws \ListBroking\APIBundle\Exception\APIException
     */
    public function processRequest(){

        // validate request parameters to check if it's empty
        $token = $this->getTokenByName($this->request->get('token_name'), 'true');
        try {
            $this->validation_service->checkEmptyFields($this->request);
            $this->checkRequestToken($token);
            foreach ($this->validators as $validator){
                $this->validations = $validator->validate($this->validations);
            }
//            ladybug_dump_die($this->validations);
            $this->saveLead();
            $response = "Lead successfully saved.";
            return $this->createJsonResponse($response);
        } catch (CoreValidationException $e) {
            $response = "Exception found - " . $e->getMessage();
            $response = $this->createJsonResponse($response, '400');
        } catch (LeadValidationException $e) {
            $response = "Exception found - " . $e->getMessage();
            $response = $this->createJsonResponse($response, '400');
        } catch (APIException $e){
            $response = "Exception found - " . $e->getMessage();
            $response = $this->createJsonResponse($response, '401');
        }
        return $response;
    }

    private function checkRequestToken($token){
        if ($token == null || $token->getToken() !=  $this->request->get('token')){
            throw new APIException("Unauthorized access.");
        }
    }

    private function saveLead(){
        if ($this->validations['repeated_lead'] != null){
            $this->saveContact($this->validations['repeated_lead']);
        } else {
            $lead = $this->request->get('lead');
            $resting_date = new \DateTime($lead['resting_date']);
            if (!isset($resting_date) || empty($resting_date)) {
                throw new APIException("No resting time defined.");
            }
            $lead = new Lead();
            $lead->setCountry($this->validations['country']);
            $lead->setIsMobile($this->validations['is_mobile']);
            $lead->setInOpposition(0);      // TODO: check if it's in opposition
            $lead->setPhone($this->validations['phone']);
            $lock = new Lock();
            $lock->setExpirationDate($resting_date);
            $lock->setType(1);
            $lock->setLead($lead);
            $lock->setIsActive(1);
            $lock->setStatus(1);
            $lead->addLocks($lock);
            $this->lead_service->addLead($lead);
            $this->saveContact($lead);
        }
    }

    /**
     * @param $lead
     * @return $this
     */
    private function saveContact($lead){
        $contact = new Contact();
        $contact->setCountry($this->validations['country']);
        $contact->setAddress($this->validations['address']);
        $contact->setBirthdate($this->validations['birthdate']);
        $contact->setEmail($this->validations['email']);
        $contact->setGender($this->contact_detail_service->getGender($this->validations['gender'], true));
        $contact->setFirstname($this->validations['firstname']);
        $contact->setLastname($this->validations['lastname']);
        $contact->setIpaddress($this->validations['ipaddress']);
        $contact->setLead($lead);
        $contact->setSubCategory($this->validations['sub_category']);
        $contact->setSource($this->validations['source']);
        $contact->setPostalcode1($this->validations['postalcode1']);
        $contact->setOwner($this->validations['owner']);

        if (isset($this->validations['postalcode2'])){
            $contact->setPostalcode2($this->validations['postalcode2']);
        }

        return $this->lead_service->addContact($contact);
    }

    /**
     * @param $response
     * @param int $code
     * @return JsonResponse
     */
    private function createJsonResponse($response, $code = 200){
        return new JsonResponse(array(
                "code" => $code,
                "response" => $response
            ), $code);
    }

    /**
     * @param $token
     * @return $this
     */
    public function addToken($token){
        $this->add(self::API_LIST, self::API_SCOPE, $this->api_token_repo, $token);
        return $this;
    }

    /**
     * @param $token_id
     * @return $this
     */
    public function removeToken($token_id){
        $this->remove(self::API_LIST, self::API_SCOPE, $this->api_token_repo, $token_id);
        return $this;
    }

    /**
     * @param $token_id
     * @param bool $hydrate
     * @return mixed|null
     */
    public function getToken($token_id, $hydrate = false){
        return $this->get(self::API_LIST, self::API_SCOPE, $this->api_token_repo, $token_id, $hydrate);
    }

    /**
     * @param $token
     * @return $this
     */
    public function updateToken($token){
        $this->update(self::API_LIST, self::API_SCOPE, $this->api_token_repo, $token);
        return $this;
    }

    /**
     * @param $token
     * @param bool $hydrate
     * @return array|mixed
     */
    public function getTokenByName($token, $hydrate = false){
        $entity = $this->api_token_repo->getTokenByName($token, $hydrate);

        return $entity;
    }
}