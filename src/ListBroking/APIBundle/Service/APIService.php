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
use ListBroking\CoreBundle\Engine\CoreValidator\CategoryValidator;
use ListBroking\CoreBundle\Engine\CoreValidator\CountryValidator;
use ListBroking\CoreBundle\Exception\CoreValidationException;
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
use ListBroking\LeadBundle\Entity\County;
use ListBroking\LeadBundle\Entity\Lead;
use ListBroking\LeadBundle\Exception\LeadValidationException;
use ListBroking\LeadBundle\Service\ContactDetailsService;
use ListBroking\LeadBundle\Service\LeadService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class APIService {

    protected $lead_service;
    protected $core_service;
    protected $form_factory;
    protected $contact_detail_service;
    protected $request;
    protected $validation_service;
    protected $validators;
    protected $lead_country;

    /**
     * @param LeadService $leadService
     * @param CoreService $coreService
     * @param ContactDetailsService $contactDetailsService
     * @param RequestStack $requestStack
     */
    public function __construct(LeadService $leadService, CoreService $coreService, ContactDetailsService $contactDetailsService, RequestStack $requestStack)
    {
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
        );
        $this->validations = array();
    }

    public function processRequest(){
        // validate request parameters to check if it's empty
        $this->validation_service->checkEmptyFields($this->request);
        try {
            foreach ($this->validators as $validator){
                $this->validations = $validator->validate($this->validations);
            }
            $this->saveLead();
        } catch (CoreValidationException $e) {
            echo "Exception found - " . $e->getMessage(), "\r\n";
            $response = new Response(); // TODO: http code and message JsonResponse
        } catch (LeadValidationException $e) {
            echo "Exception found - " . $e->getMessage(), "\r\n";
            $response = new Response(); // TODO: http code and message JsonResponse
        }
    }

    private function saveLead(){
        if ($this->validations['repeated_lead'] != null){
            $this->saveContact($this->validations['repeated_lead']);
        } else {
            $lead = new Lead();
            $lead->setCountry($this->validations['country']);
            $lead->setIsMobile(0);          // TODO: make validation to phone mobile and add it here
            $lead->setInOpposition(0);      // TODO: check if it's in opposition
            $lead->setPhone($this->validations['phone']);
            $this->lead_service->addLead($lead, true);
            $this->saveContact($lead);
        }
    }

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
}