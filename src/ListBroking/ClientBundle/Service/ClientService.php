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


use Adclick\CacheBundle\Manager\CacheManagerInterface;
use ListBroking\ClientBundle\Repository\ORM\CampaignRepository;
use ListBroking\ClientBundle\Repository\ORM\ClientRepository;

class ClientService implements ClientServiceInterface
{

    private $cache;
    private $client_repo;
    private $campaign_repo;

    function __construct(CacheManagerInterface $cache, ClientRepository $client_repo, CampaignRepository $campaign_repo)
    {
        $this->cache = $cache;
        $this->client_repo = $client_repo;
        $this->campaign_repo = $campaign_repo;
    }

    /**
     * Gets the list of Clients
     * @return mixed|null
     */
    public function getClientList()
    {
        // Check if the cache exists
        if (!$this->cache->has('client_list'))
        {
            $this->cache->beginWarmingUp('client_list', 'client');

            $clients = $this->client_repo->findAll();

            $this->cache->set('client_list', $clients);
        }

        return $this->cache->get('client_list');
    }

    /**
     * Add a Client to the list
     * @param $client
     * @return $this|mixed
     * @throws \ListBroking\DoctrineBundle\Exception\EntityClassMissingException
     */
    public function addClient($client)
    {
        // Create the new entity
        $this->client_repo->createNewEntity($client);
        $this->client_repo->flush();

        // Invalidate the cache
        if ($this->cache->has('client_list'))
        {
            $this->cache->invalidateScope('client');
        }

        //Return the service for chaining
        return $this;
    }

    /**
     * Removes a Client from the list
     * @param $id
     * @return $this|mixed
     */
    public function removeClient($id)
    {
        // Finds and removes the client
        $client = $this->client_repo->findOneById($id);
        $this->client_repo->remove($client);
        $this->client_repo->flush();

        // Invalidate the cache
        if ($this->cache->has('client_list'))
        {
            $this->cache->invalidateScope('client');
        }

        //Return the service for chaining
        return $this;
    }

    /**
     * Gets a Client by ID
     * @param $id
     * @return mixed|null
     */
    public function getClient($id)
    {
        // Check if the cache exists
        if (!$this->cache->has('client_list'))
        {
            $this->cache->beginWarmingUp('client_list', 'client');

            $clients = $this->client_repo->findAll();

            $this->cache->set('client_list', $clients);
        }

        /* iterate through the cache and select the entity */
        $clients = $this->cache->get('client_list');
        foreach ($clients as $client)
        {
            if ($client->getId() == $id)
            {
                return $client;
            }
        }

        return null;
    }

    /**
     * Gets the list of Campaigns
     * @return mixed
     */
    public function getCampaignList()
    {
        // Check if the cache exists
        if (!$this->cache->has('campaign_list'))
        {
            $this->cache->beginWarmingUp('campaign_list', 'campaign');

            $clients = $this->campaign_repo->findAll();

            $this->cache->set('campaign_list', $clients);
        }

        return $this->cache->get('campaign_list');
    }

    /**
     * Adds a Campaign to the list
     * @param $campaign
     * @return $this|mixed
     * @throws \ListBroking\DoctrineBundle\Exception\EntityClassMissingException
     */
    public function addCampaign($campaign)
    {
        // Create the new entity
        $this->campaign_repo->createNewEntity($campaign);
        $this->campaign_repo->flush();

        // Invalidate the cache
        if ($this->cache->has('campaign_list'))
        {
            $this->cache->invalidateScope('campaign');
        }

        //Return the service for chaining
        return $this;
    }

    /**
     * Removes a Campaign from the list
     * @param $id
     * @return $this|mixed
     */
    public function removeCampaign($id)
    {
        // Finds and removes the client
        $campaign = $this->campaign_repo->findOneById($id);
        $this->campaign_repo->remove($campaign);
        $this->campaign_repo->flush();

        // Invalidate the cache
        if ($this->cache->has('campaign_list'))
        {
            $this->cache->invalidateScope('campaign');
        }

        //Return the service for chaining
        return $this;
    }

    /**
     * Gets a Campaign by ID
     * @param $id
     * @return mixed|null
     */
    public function getCampaign($id)
    {
        // Check if the cache exists
        if (!$this->cache->has('campaign_list'))
        {
            $this->cache->beginWarmingUp('campaign_list', 'campaign');

            $clients = $this->campaign_repo->findAll();

            $this->cache->set('campaign_list', $clients);
        }

        /* iterate through the cache and select the entity */
        $campaigns = $this->cache->get('campaign_list');
        foreach ($campaigns as $campaign)
        {
            if ($campaign->getId() == $id)
            {
                return $campaign;
            }
        }

        return null;
    }
}