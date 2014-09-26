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
     * Gets list of clients
     * @param bool $only_active
     * @return mixed
     */
    public function getClientList($only_active = true);

    /**
     * Gets a single client
     * @param $id
     * @return mixed
     */
    public function getClient($id);

    /**
     * Adds a single client
     * @param $client
     * @return mixed
     */
    public function addClient($client);

    /**
     * Removes a single client
     * @param $id
     * @return mixed
     */
    public function removeClient($id);

    /**
     * Updates a single country
     * @param $client
     * @return mixed
     */
    public function updateClient($client);

    /**
     * Gets list of campaigns
     * @param bool $only_active
     * @return mixed
     */
    public function getCampaignList($only_active = true);

    /**
     * Gets a single campaign
     * @param $id
     * @return mixed
     */
    public function getCampaign($id);

    /**
     * Adds a single campaign
     * @param $campaign
     * @return mixed
     */
    public function addCampaign($campaign);

    /**
     * Removes a single campaign
     * @param $id
     * @return mixed
     */
    public function removeCampaign($id);

    /**
     * Updates a single campaign
     * @param $campaign
     * @return mixed
     */
    public function updateCampaign($campaign);

} 