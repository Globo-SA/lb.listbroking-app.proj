<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\ClientBundle\Repository;

interface ClientRepositoryInterface {

    /**
     * @param bool $only_active
     * @return mixed
     */
    public function findAll($only_active = true);
}