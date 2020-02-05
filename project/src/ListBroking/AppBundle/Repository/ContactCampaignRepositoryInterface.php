<?php

namespace ListBroking\AppBundle\Repository;

interface ContactCampaignRepositoryInterface
{
    /**
     * @param int $extractionId
     *
     * @return mixed
     */
    public function generateHistory(int $extractionId);
}
