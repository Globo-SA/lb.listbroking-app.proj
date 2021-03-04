<?php

namespace ListBroking\AppBundle\Service\Factory;

use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\ConsentRevalidation;

interface ConsentRevalidationFactoryInterface
{
    /**
     * Creates a new "consent" ConsentRevalidation object
     *
     * @param Contact $contact
     *
     * @return ConsentRevalidation
     */
    public function createIVRRevalidation(Contact $contact): ConsentRevalidation;
}