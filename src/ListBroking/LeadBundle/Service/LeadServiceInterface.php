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


use ListBroking\ExtractionBundle\Entity\Extraction;

interface LeadServiceInterface {
    public function getLeadList($only_active = true);

    public function getLead($id);

    public function addLead($lead);

    public function removeLead($id);

    public function updateLead($lead);

    public function getContactList($only_active = true);

    public function getContact($id);

    public function addContact($contact);

    public function removeContact($id);

    public function updateContact($contact);

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
} 