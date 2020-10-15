<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\SubCategory;

/**
 * ListBroking\AppBundle\Repository\SubCategoryRepositoryInterface
 */
interface SubCategoryRepositoryInterface
{
    /**
     * Get district by its name
     *
     * @param array $names
     *
     * @return SubCategory[]
     */
    public function getByName(array $names): ?array;
}
