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


    /**
     * Removes locks by expiration date
     * NOTE: An EventListener is used to send
     * the locks to a _log table before there are removed
     * @param $days
     * @return mixed
     */
    public function removeByExpirationDate($days);
} 