<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Service\Base\BaseServiceInterface;

interface CampaignServiceInterface extends BaseServiceInterface
{

    /**
     * Create new campaign
     *
     * @param array $campaignData
     *
     * @return Campaign
     */
    public function addCampaign(array $campaignData): Campaign;
}
