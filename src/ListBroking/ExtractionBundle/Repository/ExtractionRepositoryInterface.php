<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\ExtractionBundle\Repository;


interface ExtractionRepositoryInterface {

    /**
     * Associates multiple contacts to an extraction
     * @param $extraction Extraction
     * @param $contacts
     * @param $merge
     * @return mixed
     */
    public function addContacts($extraction, $contacts, $merge);
}