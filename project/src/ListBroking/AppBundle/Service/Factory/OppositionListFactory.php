<?php

namespace ListBroking\AppBundle\Service\Factory;

use ListBroking\AppBundle\Entity\OppositionList;

/**
 * Class OppositionListFactory
 */
class OppositionListFactory
{

    /**
     * Creates a new OppositionList object
     *
     * @param string $type
     * @param string $phone
     *
     * @return OppositionList
     */
    public function create(string $type, string $phone): OppositionList
    {

        $oppositionList = new OppositionList();
        $oppositionList->setType($type);
        $oppositionList->setPhone($phone);

        return $oppositionList;
    }
}
