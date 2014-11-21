<?php
/**
 *
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service;


use Doctrine\ORM\EntityManager;
use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\LockBundle\Engine\LockEngine;

/**
 * This service can not use a cache system, as locks are too volatile.
 * So every action should be a database call.
 * Class LeadService
 * @package ListBroking\AppBundle\Service
 */
class LeadService implements LeadServiceInterface {

    /**
     * @var EntityManager
     */
    private $em;

    function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param $id
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getLead($id){

        return  $this->em->getRepository('ListBrokingAppBundle:Lead')->findOneBy(array('id' => $id));
    }

    /**
     * @param $phone
     * @return mixed
     */
    public function getLeadByPhone($phone){

        return  $this->em->getRepository('ListBrokingAppBundle:Lead')->findOneBy(array('phone' => $phone));
    }

    /**
     * @param $entity
     * @return $this
     */
    public function addLead($entity){
        if($entity)
        {
            $this->em->persist($entity);
            $this->em->flush();
        }
    }

    /**
     * @param $entity Lead
     * @return $this
     */
    public function removeLead($entity){
        // Remove entity
        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
     * @param $entity
     * @return $this
     */
    public function updateLead($entity){

        $this->em->persist($entity);
        $this->em->flush();
    }

    /**
     * @param $id
     * @return \ListBroking\AppBundle\Entity\Lead
     */
    public function getContact($id){

        return  $this->em->getRepository('ListBrokingAppBundle:Lead')->findOneBy(array('id' => $id));
    }

    /**
     * @param $email
     * @return array|mixed
     */
    public function getContactsByEmail($email){

        return  $this->em->getRepository('ListBrokingAppBundle:Lead')->findOneBy(array('email' => $email));
    }


    /**
     * Returns Fields from Contact Table
     */
    public function getContactFields(){

        return $this->em->getClassMetadata('ListBroking\AppBundle\Entity\Contact')->columnNames;
    }

    /**
     * @param $entity
     * @return $this
     */
    public function addContact($entity){
        if($entity)
        {
            $this->em->persist($entity);
            $this->em->flush();
        }
    }

    /**
     * @param $entity
     * @return $this
     */
    public function removeContact($entity){

        // Remove entity
        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
     * @param $entity
     * @return $this
     */
    public function updateContact($entity){

        $this->em->persist($entity);
        $this->em->flush();
    }

    /**
     * Group leads by lock and count them
     * @return array
     */
    public function countByLock()
    {
        return $this->em->getRepository('ListBrokingAppBundle:Lock')->countByLock();
    }

    /**
     * Gets all the contacts of a given Extraction with
     * all the dimensions eagerly loaded
     * @param Extraction $extraction
     * @return mixed
     */
    public function getExtractionContacts(Extraction $extraction){
        return $this->em->getRepository('ListBrokingAppBundle:Lock')->getExtractionContacts($extraction);
    }

    /**
     * Adds a single lock
     * @param $entity
     * @return mixed
     */
    public function addLock($entity){
        if($entity)
        {
            $this->em->persist($entity);
            $this->em->flush();
        }
    }

    /**
     * Removes a single lock
     * @param $entity
     * @return mixed
     */
    public function removeLock($entity){

        $this->em->persist($entity);
        $this->em->flush();
    }

    /**
     * Removes expire locks
     * NOTE: Locks are always moved to a _log table
     * @param $days
     * @return int
     */
    public function removeExpiredLocks($days)
    {
        return $this->lock_repo->removeByExpirationDate($days);
    }

    /**
     * Updates a single country
     * @param $entity
     * @return mixed
     */
    public function updateLock($entity){

        $this->em->persist($entity);
        $this->em->flush();
    }
}