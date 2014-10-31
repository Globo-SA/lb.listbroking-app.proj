<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LeadBundle\Engine\LeadValidator;


use ListBroking\LeadBundle\Exception\LeadValidationException;
use Symfony\Component\HttpFoundation\Request;

class ContactValidator extends BaseValidator {

    /**
     * @param $service
     * @param $lead
     */
    public function __construct($service, $lead)
    {
        parent::__construct($service, $lead);
        $this->fields = $this->service->getContactFields();
        // TODO: ADVANCED CONFIGURATION TO ALLOW TO CHOOSE WHICH FIELDS TO UNSET
        unset($this->fields['id']);
        unset($this->fields['created_at']);
        unset($this->fields['updated_at']);
        unset($this->fields['postalcode2']);
        unset($this->fields['ipaddress']);
        unset($this->fields['email']);
    }

    /**
     * @param $validations
     * @return mixed
     * @throws LeadValidationException
     */
    public function validate($validations)
    {
        if (isset($this->lead['email']))
        {
            $this->validateEmail($this->lead['email']);
            if (isset($validations['repeated_lead'])){
                $this->checkRepeatedContactLead($validations, $this->lead['email']);
            }
        } else {
            $validations['email'] = null;
        }

        foreach ($this->fields as $key => $value){
            if (!isset($this->lead[$key])){
                throw new LeadValidationException("Field lead[" . $key . "] not sent.\n");
            }
            parent::validateEmpty($this->lead[$key], $key);
            $validations[$key] = $this->lead[$key];
        }

        $validations['email'] = $this->lead['email'];

        $validations['birthdate'] = str_replace('/', '-', $validations['birthdate']);
        $this->validateBirthdate($validations['birthdate']);
        $validations['birthdate'] = new \DateTime($this->reformatDate($validations['birthdate']));
        if (isset($this->lead['postalcode2']) && !empty($this->lead['postalcode2'])){
            $validations['postalcode2'] = $this->lead['postalcode2'];
        }

        if (isset($this->lead['ipaddress']) && !empty($this->lead['ipaddress'])){
            $validations['ipaddress'] = $this->lead['ipaddress'];
        }

        return $validations;
    }

    /**
     * @param $birthdate
     * @return bool
     * @throws LeadValidationException
     */
    private function validateBirthdate($birthdate){
        if (!preg_match('/\d{4}-(0[1-9]|1[0-2])-([0-2][0-9]|3[0-1])/', $birthdate) && !preg_match('/([0-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-\d{4}/', $birthdate) && !preg_match('/(0[1-9]|1[0-2])-([0-2][0-9]|3[0-1])-\d{4}/', $birthdate)){
            ladybug_dump($birthdate);
            throw new LeadValidationException("Birthdate format not correct.\n");
        }
        return true;
    }

    /**
     * @param $date
     * @return bool|string
     */
    private function reformatDate($date){
        return date("d-m-Y", strtotime($date));
    }


    /**
     * @param $validations
     * @param $email
     * @return bool
     * @throws LeadValidationException
     */
    private function checkRepeatedContactLead($validations, $email){
        $lead = $validations['repeated_lead'];
        $contacts = $lead->getContacts();
        if ($contacts->count()){
            foreach ($contacts->getIterator() as $contact){
                $owner_id = $validations['owner']->getId();
                if ( $email == $contact->getEmail() && $contact->getOwner()->getId() == $owner_id ){
                    throw new LeadValidationException("Lead is repeated by phone and email, for the owner " . $validations['owner']->getName() . ".");
                }
            }
        }
        return false;
    }

    /**
     * @param $email
     * @throws LeadValidationException
     */
    private function validateEmail($email){
        if (!preg_match('/^[-a-z0-9~!$%^&*_=+}{\'?]+(\\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z]{2})|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i', $email)){
            throw new LeadValidationException("Invalid email.");
        }
    }
}