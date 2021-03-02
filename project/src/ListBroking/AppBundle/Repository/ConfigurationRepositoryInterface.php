<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\Configuration;

interface ConfigurationRepositoryInterface
{
    /**
     * @param string $name
     *
     * @return Configuration|null
     */
    public function findOneByName(string $name): ?Configuration;
}