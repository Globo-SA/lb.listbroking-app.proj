<?php

namespace ListBroking\AppBundle\Service\Factory;

use ListBroking\AppBundle\Entity\Client;
use ListBroking\AppBundle\Entity\ClientNotification;
use ListBroking\AppBundle\Entity\Lead;

class ClientNotificationFactory implements ClientNotificationFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(Client $client, Lead $lead, string $type, string $campaignIds): ClientNotification
    {
        $clientNotification = new ClientNotification();

        $clientNotification->setClient($client);
        $clientNotification->setLead($lead);
        $clientNotification->setCampaigns($campaignIds);
        $clientNotification->setType($type);

        return $clientNotification;
    }
}