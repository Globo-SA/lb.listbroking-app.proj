<?php

namespace ListBroking\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ListBroking\AppBundle\Entity\Configuration;

class ConfigurationRepository extends EntityRepository implements ConfigurationRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function findOneByName(string $name): ?Configuration
    {
        $configuration = $this->findOneBy(['name' => $name]);

        return $configuration instanceof Configuration
            ? $configuration
            : null;
    }
}