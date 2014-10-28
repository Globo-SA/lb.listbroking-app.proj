<?php
/**
 * 
 * @author     Pedro Tentugal <pedro.tentugal@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\LeadBundle\Repository;


interface LeadRepositoryInterface {

    /**
     * @param $phone
     * @param bool $hydrate
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLeadByPhone($phone, $hydrate = false);

    /**
     * Group leads by lock and count them
     * @return array
     */
    public function countByLock();
} 