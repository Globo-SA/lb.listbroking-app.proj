<?php

namespace ListBroking\AppBundle\Service\External;

use ListBroking\AppBundle\Entity\Contact;

interface IntegromatServiceInterface
{
    /**
     * @param Contact $contact
     *
     * @return bool
     */
    public function requestOpposition(Contact $contact): bool;
}