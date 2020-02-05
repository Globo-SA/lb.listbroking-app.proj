<?php

namespace ListBroking\AppBundle\Service\Factory;

use ListBroking\AppBundle\Entity\Country;
use ListBroking\AppBundle\Entity\Lead;

/**
 * Class LeadFactory
 */
class LeadFactory
{

    /**
     * Creates a new Lead object
     *
     * @param string  $phone
     * @param Country $country
     * @param bool    $isMobile
     * @param bool    $isInOppositionList
     *
     * @return Lead
     */
    public function create(string $phone, Country $country, bool $isMobile = false, bool $isInOppositionList = false): Lead
    {
        $lead = new Lead();
        $lead->setPhone($phone);
        $lead->setCountry($country);
        $lead->setIsMobile($isMobile);
        $lead->setInOpposition($isInOppositionList);

        return $lead;
    }
}
