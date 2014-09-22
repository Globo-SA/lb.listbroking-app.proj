<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\ClientBundle\Service;


interface ClientServiceInterface {

    /**
     * @param bool $only_active
     * @return mixed
     */
    public function getClientList($only_active = true);

    /**
     * Add a Client to the list
     * @param $variable
     * @return mixed
     */
    public function addClient($variable);

    /**
     * Removes a Client from the list
     * @param $id
     * @return mixed
     */
    public function removeClient($id);

    /**
     * @param $id
     * @param $only_active
     * @return mixed
     */
    public function getClient($id, $only_active = tru);

    /**
     * @param bool $only_active
     * @return mixed
     */
    public function getCampaignList($only_active = true);

    /**
     * Adds a Campaign to the list
     * @param $campaign
     * @return mixed
     */
    public function addCampaign($campaign);

    /**
     * Removes a Campaign from the list
     * @param $id
     * @return mixed
     */
    public function removeCampaign($id);

    /**
     * @param $id
     * @param $only_active
     * @return mixed
     */
    public function getCampaign($id, $only_active = tru);

} 