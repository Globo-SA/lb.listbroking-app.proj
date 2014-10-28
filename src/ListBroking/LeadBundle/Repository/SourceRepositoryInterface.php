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


interface SourceRepositoryInterface {
    /**
     * @param $source_name
     * @param bool $hydrate
     * @return mixed
     */
    public function getSourceByName($source_name, $hydrate = false);

    /**
     * @param $external_id
     * @param bool $hydrate
     * @return mixed
     */
    public function getByExternalId($external_id, $hydrate = false);
} 