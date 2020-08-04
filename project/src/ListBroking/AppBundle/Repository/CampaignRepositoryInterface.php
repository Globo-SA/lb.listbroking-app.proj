<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Entity\Client;

interface CampaignRepositoryInterface
{
    /**
     * Persist new Campaign
     *
     * @param Client $client
     * @param array  $campaignData
     *
     * @return Campaign
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addCampaign(Client $client, array $campaignData): Campaign;
}
