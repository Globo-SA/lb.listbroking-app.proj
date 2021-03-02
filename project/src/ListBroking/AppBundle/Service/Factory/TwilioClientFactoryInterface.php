<?php

namespace ListBroking\AppBundle\Service\Factory;

use Twilio\Rest\Client as TwilioRestClient;

interface TwilioClientFactoryInterface
{
    /**
     * Creates a new twilio REST client
     *
     * @return TwilioRestClient
     */
    public function createRestClient(): TwilioRestClient;
}