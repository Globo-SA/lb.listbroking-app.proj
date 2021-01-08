<?php

namespace ListBroking\AppBundle\Service\Factory;

use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\ConsentRevalidation;

class ConsentRevalidationFactory implements ConsentRevalidationFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createIVRRevalidation(Contact $contact): ConsentRevalidation
    {
        $consentRevalidation = new ConsentRevalidation();

        $consentRevalidation->setStatus(ConsentRevalidation::STATUS_NEW);
        $consentRevalidation->setType(ConsentRevalidation::TYPE_IVR);
        $consentRevalidation->setContact($contact);

        return $consentRevalidation;
    }
}