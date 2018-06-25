<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Lead;

interface ClientNotificationServiceInterface
{
    /**
     * @param Lead $lead
     *
     * @return mixed
     */
    public function notifyClientToObfuscateLead(Lead $lead);
}