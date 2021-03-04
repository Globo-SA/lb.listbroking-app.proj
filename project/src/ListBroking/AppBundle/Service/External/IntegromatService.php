<?php

namespace ListBroking\AppBundle\Service\External;

use Guzzle\Service\Client;
use ListBroking\AppBundle\Entity\Contact;
use Monolog\Logger;

class IntegromatService implements IntegromatServiceInterface
{
    /**
     * @var Client
     */
    private $guzzleClient;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $integromatIurisWebhook;

    /**
     * IntegromatService constructor.
     *
     * @param Client $guzzleClient
     * @param Logger $logger
     * @param string $integromatIurisWebhook
     */
    public function __construct(Client $guzzleClient, Logger $logger, string $integromatIurisWebhook)
    {
        $this->guzzleClient           = $guzzleClient;
        $this->logger                 = $logger;
        $this->integromatIurisWebhook = $integromatIurisWebhook;
    }

    /**
     * {@inheritDoc}
     */
    public function requestOpposition(Contact $contact): bool
    {
        $this->logger->info('Requesting opposition', [
            'contact_id' => $contact->getId()
        ]);

        $name = sprintf('%s %s', $contact->getFirstname(), $contact->getLastname());

        $postBody = [
            'name'   => $name,
            'email'  => $contact->getEmail(),
            'phone'  => $contact->getLead()->getPhone(),
            'region' => $contact->getCountry()->getName()
        ];

        $response = $this->guzzleClient
            ->post($this->integromatIurisWebhook, [], $postBody)
            ->send();

        return $response->getStatusCode() === 200;
    }
}