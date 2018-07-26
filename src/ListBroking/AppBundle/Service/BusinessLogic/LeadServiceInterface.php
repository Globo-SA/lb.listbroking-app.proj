<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Lead;

interface LeadServiceInterface
{
    /**
     * Find leads by email or phone
     *
     * @param string $email
     * @param string $phone
     *
     * @return Lead[]
     */
    public function getLeads(string $email, string $phone): array;
}