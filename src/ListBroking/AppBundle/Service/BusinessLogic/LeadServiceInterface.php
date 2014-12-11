<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\BusinessLogic;


use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\Lead;

interface LeadServiceInterface
{

    /**
     * @param $id
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getLead($id);
    /**
     * @param $phone
     * @return mixed
     */
    public function getLeadByPhone($phone);

    /**
     * @param $entity
     * @return $this
     */
    public function addLead($entity);

    /**
     * @param $entity Lead
     * @return $this
     */
    public function removeLead($entity);

    /**
     * @param $entity
     * @return $this
     */
    public function updateLead($entity);

    /**
     * @param $id
     * @return \ListBroking\AppBundle\Entity\Lead
     */
    public function getContact($id);

    /**
     * @param $email
     * @return array|mixed
     */
    public function getContactsByEmail($email);


    /**
     * Returns Fields from Contact Table
     */
    public function getContactFields();

    /**
     * @param $entity
     * @return $this
     */
    public function addContact($entity);

    /**
     * @param $entity
     * @return $this
     */
    public function removeContact($entity);

    /**
     * @param $entity
     * @return $this
     */
    public function updateContact($entity);

    /**
     * Group leads by lock and count them
     * @return array
     */
    public function countByLock();

    /**
     * Gets all the contacts of a given Extraction with
     * all the dimensions eagerly loaded
     * @param Extraction $extraction
     * @return mixed
     */
    public function getExtractionContacts(Extraction $extraction);

    /**
     * Adds a single lock
     * @param $entity
     * @return mixed
     */
    public function addLock($entity);

    /**
     * Removes a single lock
     * @param $entity
     * @return mixed
     */
    public function removeLock($entity);

    /**
     * Removes expire locks
     * NOTE: Locks are always moved to a _log table
     * @param $days
     * @return int
     */
    public function removeExpiredLocks($days);

    /**
     * Updates a single country
     * @param $entity
     * @return mixed
     */
    public function updateLock($entity);
}