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
     * Gets the list of Clients
     * @return mixed
     */
    public function getClientList();

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
     * Gets a Client by ID
     * @param $id
     * @return mixed
     */
    public function getClient($id);

    /**
     * Gets the list of Campaigns
     * @return mixed
     */
    public function getCampaignList();

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
     * Gets a Campaign by ID
     * @param $id
     * @return mixed
     */
    public function getCampaign($id);

} 