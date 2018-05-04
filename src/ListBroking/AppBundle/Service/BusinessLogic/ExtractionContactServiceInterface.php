<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Contact;

interface ExtractionContactServiceInterface
{
    /**
     * @param Contact $contact
     *
     * @return array
     */
    public function findContactExtractions(Contact $contact): array;
}