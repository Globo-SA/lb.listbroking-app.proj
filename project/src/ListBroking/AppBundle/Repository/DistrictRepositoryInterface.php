<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\District;

/**
 * ListBroking\AppBundle\Repository\DistrictRepositoryInterface
 */
interface DistrictRepositoryInterface
{
    /**
     * Get district by its name
     *
     * @param array $names
     *
     * @return District[]
     */
    public function getByName(array $names): ?array;
}
