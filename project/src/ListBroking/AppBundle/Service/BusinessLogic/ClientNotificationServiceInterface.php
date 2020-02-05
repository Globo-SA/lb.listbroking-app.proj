<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

interface ClientNotificationServiceInterface
{
    /**
     * Notify each client about the collection of contacts that requested the right to be forgotten
     *
     * @param array $leads
     *
     * @return void
     */
    public function notifyClientsToRemoveLeads(array $leads): void;
}