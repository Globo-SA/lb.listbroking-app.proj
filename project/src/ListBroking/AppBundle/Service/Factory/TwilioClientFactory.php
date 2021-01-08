<?php

namespace ListBroking\AppBundle\Service\Factory;

use Twilio\Exceptions\ConfigurationException;
use Twilio\Rest\Client as TwilioRestClient;

class TwilioClientFactory implements TwilioClientFactoryInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * TwilioRestClientFactory constructor.
     *
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ConfigurationException
     */
    public function createRestClient(): TwilioRestClient
    {
        return new TwilioRestClient($this->username, $this->password);
    }
}