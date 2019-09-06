<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\Lead;

interface LeadRepositoryInterface
{
    /**
     * Finds leads in history by phone. The phone is unique by country.
     *
     * @param string $phone
     *
     * @return Lead[]
     */
    public function getByPhone(string $phone): array;
}