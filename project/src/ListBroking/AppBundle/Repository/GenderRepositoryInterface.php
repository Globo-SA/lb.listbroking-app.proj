<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\Gender;

/**
 * ListBroking\AppBundle\Repository\GenderRepositoryInterface
 */
interface GenderRepositoryInterface
{
    /**
     * Get source by its external id
     *
     * @param array $names
     *
     * @return Gender[]
     */
    public function getByName(array $names): ?array;
}
