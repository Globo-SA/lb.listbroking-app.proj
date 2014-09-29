<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LockBundle\Repository;


interface LockRepositoryInterface {

    /**
     * Finds all locks of a given lead
     * @param $ids
     * @return mixed
     */
    public function findByLead($ids);

} 