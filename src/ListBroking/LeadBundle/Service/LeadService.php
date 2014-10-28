<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LeadBundle\Service;

use ListBroking\LeadBundle\Repository\ORM\ContactRepository;
use ListBroking\LeadBundle\Repository\ORM\LeadRepository;
use Symfony\Component\Form\FormFactory;

class LeadService implements LeadServiceInterface {
    private $contact_repo;

    private $lead_repo;

    private $form_factory;

    /**
     * @param ContactRepository $contact_repo
     * @param LeadRepository $lead_repo
     * @param FormFactory $form_factory
     */
    function __construct(
        ContactRepository $contact_repo,
        LeadRepository $lead_repo,
        FormFactory $form_factory
    ) {
        $this->contact_repo = $contact_repo;
        $this->lead_repo    = $lead_repo;
        $this->form_factory = $form_factory;
    }

    /**
     * @param bool $only_active
     * @return array
     */
    public function getLeadList($only_active = true){
        $enteties = $this->lead_repo->findAll();

        return  $enteties;
    }

    /**
     * @param $id
     * @param bool $hydrate
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getLead($id, $hydrate = false){
        $entity = $this->lead_repo->findOneById($id);

        return $entity;
    }

    /**
     * @param $phone
     * @param bool $hydrate
     * @return mixed
     */
    public function getLeadByPhone($phone, $hydrate = false){
        $entity = $this->lead_repo->getLeadByPhone($phone, $hydrate);

        return $entity;
    }

    /**
     * @param $lead
     * @return $this
     * @throws \ListBroking\DoctrineBundle\Exception\EntityClassMissingException
     * @throws \ListBroking\DoctrineBundle\Exception\EntityObjectInstantiationException
     */
    public function addLead($lead){
        $this->lead_repo->createNewEntity($lead);
        $this->lead_repo->flush();

        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function removeLead($id){
        $entity = $this->lead_repo->findOneById($id);
        $this->lead_repo->remove($entity);
        $this->lead_repo->flush();

        return $this;
    }

    /**
     * @param $lead
     * @return $this
     */
    public function updateLead($lead){
        $this->lead_repo->merge($lead);
        $this->lead_repo->flush();

        return $this;
    }

    /**
     * @param bool $only_active
     * @return array
     */
    public function getContactList($only_active = true){
        $entities = $this->contact_repo->findAll();

        return $entities;
    }

    /**
     * @param $id
     * @param bool $hydrate
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getContact($id, $hydrate = false){
        $entity = $this->contact_repo->findOneById($id);

        return $entity;
    }

    /**
     * @param $email
     * @param bool $hydrate
     * @return array|mixed
     */
    public function getContactsByEmail($email, $hydrate = false){
        $entity = $this->contact_repo->getContactsByEmail($email, $hydrate);

        return $entity;
    }

    /**
     * Returns Fields from Contact Table
     */
    public function getContactFields(){
        $entity = $this->contact_repo->getContactFields();

        return $entity;
    }

    /**
     * @param $contact
     * @return $this
     * @throws \ListBroking\DoctrineBundle\Exception\EntityClassMissingException
     * @throws \ListBroking\DoctrineBundle\Exception\EntityObjectInstantiationException
     */
    public function addContact($contact){
        $this->contact_repo->createNewEntity($contact);
        $this->contact_repo->flush();
        ladybug_dump_die($contact);

        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function removeContact($id){
        $this->contact_repo->remove($id);
        $this->contact_repo->flush();;

        return $this;
    }

    /**
     * @param $contact
     * @return $this
     */
    public function updateContact($contact){
        $this->contact_repo->merge($contact);
        $this->contact_repo->flush();

        return $this;
    }
}