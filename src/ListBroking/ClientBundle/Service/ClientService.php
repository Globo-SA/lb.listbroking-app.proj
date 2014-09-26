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
use ListBroking\CoreBundle\Service\BaseService;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClientService extends BaseService implements ClientServiceInterface
{

    private $client_repo;
    private $campaign_repo;

    const CLIENT_LIST = 'client_list';
    const CLIENT_SCOPE = 'client';

    const CAMPAIGN_LIST = 'campaign_list';
    const CAMPAIGN_SCOPE = 'campaign';

    function __construct(CacheManagerInterface $cache, ValidatorInterface $validator, ClientRepository $client_repo, CampaignRepository $campaign_repo)
    {
        parent::__construct($cache, $validator);
        $this->client_repo = $client_repo;
        $this->campaign_repo = $campaign_repo;
    }

    /**
     * Gets list of clients
     * @param bool $only_active
     * @return mixed
     */
    public function getClientList($only_active = true){
        return $this->getList(self::CLIENT_LIST, self::CLIENT_SCOPE, $this->client_repo, $only_active);
    }

    /**
     * Gets a single client
     * @param $id
     * @return mixed
     */
    public function getClient($id){
        return $this->get(self::CLIENT_LIST, self::CLIENT_SCOPE, $this->client_repo, $id);
    }

    /**
     * Adds a single client
     * @param $client
     * @return mixed
     */
    public function addClient($client){
        $this->add(self::CLIENT_LIST, self::CLIENT_SCOPE, $this->client_repo, $client);
        return $this;
    }

    /**
     * Removes a single client
     * @param $id
     * @return mixed
     */
    public function removeClient($id){
        $this->remove(self::CLIENT_LIST, self::CLIENT_SCOPE, $this->client_repo, $id);
        return $this;
    }

    /**
     * Updates a single country
     * @param $client
     * @return mixed
     */
    public function updateClient($client){
        $this->update(self::CLIENT_LIST, self::CLIENT_SCOPE, $this->client_repo, $client);
        return $this;
    }

    /**
     * Gets list of campaigns
     * @param bool $only_active
     * @return mixed
     */
    public function getCampaignList($only_active = true){
        return $this->getList(self::CAMPAIGN_LIST, self::CAMPAIGN_SCOPE, $this->campaign_repo, $only_active);
    }

    /**
     * Gets a single campaign
     * @param $id
     * @return mixed
     */
    public function getCampaign($id){
        return $this->get(self::CAMPAIGN_LIST, self::CAMPAIGN_SCOPE, $this->campaign_repo, $id);
    }

    /**
     * Adds a single campaign
     * @param $campaign
     * @return mixed
     */
    public function addCampaign($campaign){
        $this->add(self::CAMPAIGN_LIST, self::CAMPAIGN_SCOPE, $this->campaign_repo, $campaign);
        return $this;
    }

    /**
     * Removes a single campaign
     * @param $id
     * @return mixed
     */
    public function removeCampaign($id){
        $this->remove(self::CAMPAIGN_LIST, self::CAMPAIGN_SCOPE, $this->campaign_repo, $id);
        return $this;
    }

    /**
     * Updates a single campaign
     * @param $campaign
     * @return mixed
     */
    public function updateCampaign($campaign){
        $this->update(self::CAMPAIGN_LIST, self::CAMPAIGN_SCOPE, $this->campaign_repo, $campaign);
        return $this;
    }


}