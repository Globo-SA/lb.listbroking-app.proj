<?php

namespace ListBroking\AppBundle\Service\Factory;

use ListBroking\AppBundle\Entity\Client;
use ListBroking\AppBundle\Entity\ClientNotification;
use ListBroking\AppBundle\Entity\Lead;

interface ClientNotificationFactoryInterface
{
    /**
     * Creates a Client Notification Object
     *
     * @param Client $client
     * @param Lead   $lead
     * @param string $type
     * @param string $campaignIds
     *
     * @return ClientNotification
     */
    public function create(Client $client, Lead $lead, string $type, string $campaignIds): ClientNotification;
}