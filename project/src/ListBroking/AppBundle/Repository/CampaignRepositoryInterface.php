<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Entity\Client;

interface CampaignRepositoryInterface
{
    /**
     * Persist new Campaign
     *
     * @param Client   $client
     * @param int|null $accountId
     * @param array    $campaignData
     *
     * @return Campaign
     */
    public function createCampaign(Client $client, ?int $accountId, array $campaignData): Campaign;
}
