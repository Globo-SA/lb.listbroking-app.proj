<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Entity\Client;

class CampaignRepository extends EntityRepository implements CampaignRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createCampaign(Client $client, array $campaignData): Campaign
    {
        $newCampaign = new Campaign();
        $newCampaign->setName($campaignData[Campaign::NAME]);
        $newCampaign->setClient($client);

        $description = $campaignData[Campaign::DESCRIPTION] ?? $campaignData[Campaign::NAME];
        $newCampaign->setDescription($description);

        $notificationEmailAddress = $campaignData[Campaign::NOTIFICATION_EMAIL_ADDRESS] ?? $client->getEmailAddress();
        $newCampaign->setNotificationEmailAddress($notificationEmailAddress);

        if ($campaignData[Campaign::EXTERNAL_ID] !== null) {
            $newCampaign->setExternalId($campaignData[Campaign::EXTERNAL_ID]);
        }

        $this->getEntityManager()->persist($newCampaign);
        $this->getEntityManager()->flush();

        return $newCampaign;
    }
}
