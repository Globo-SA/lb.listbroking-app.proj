<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\Contact;

interface ExtractionContactRepositoryInterface
{
    /**
     * Return a list of extractions from a given contact
     *
     * @param Contact $contact
     *
     * @return array
     */
    public function findContactExtractions(Contact $contact): array;
}