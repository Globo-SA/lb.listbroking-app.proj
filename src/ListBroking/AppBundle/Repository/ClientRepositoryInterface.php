<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\Client;

interface ClientRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return Client|null
     */
    public function getById(int $id): ?Client;
}