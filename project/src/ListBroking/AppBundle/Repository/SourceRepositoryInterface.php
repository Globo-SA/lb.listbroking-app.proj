<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\Source;

/**
 * ListBroking\AppBundle\Repository\SourceRepositoryInterface
 */
interface SourceRepositoryInterface
{
    /**
     * Get source by its external id
     *
     * @param string $externalId
     *
     * @return Source|null
     */
    public function getByExternalId(string $externalId) : ?Source;
}
