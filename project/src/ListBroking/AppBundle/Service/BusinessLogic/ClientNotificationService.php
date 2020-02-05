<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use Adclick\Components\GDPR\Mailer\MailerGdprInterface;
use ListBroking\AppBundle\Entity\Client;
use ListBroking\AppBundle\Entity\ClientNotification;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Repository\ClientRepositoryInterface;
use ListBroking\AppBundle\Repository\ExtractionContactRepositoryInterface;
use ListBroking\AppBundle\Service\Base\BaseService;
use ListBroking\AppBundle\Service\Factory\ClientNotificationFactoryInterface;

class ClientNotificationService extends BaseService implements ClientNotificationServiceInterface
{
    private const NOTIFY_CLIENT_ERROR_MESSAGE      = '#LB-0042# Unable to notify client to obfuscate lead';
    private const REMOVE_LEAD_NOTIFICATION_SUBJECT = 'Right to be forgotten request';
    private const CLIENT_ID                        = 'client_id';
    private const PHONE_KEY                        = 'phone';
    private const EMAILS_KEY                       = 'emails';
    private const CAMPAIGNS_NAMES_KEY              = 'campaigns_names';
    private const CAMPAIGNS_IDS_KEY                = 'campaigns_ids';

    /**
     * @var ClientRepositoryInterface
     */
    private $clientRepository;

    /**
     * @var ExtractionContactRepositoryInterface
     */
    private $extractionContactRepository;

    /**
     * @var ClientNotificationFactoryInterface
     */
    private $clientNotificationFactory;

    /**
     * @var MailerGdprInterface
     */
    private $mailerGdpr;

    /**
     * ClientNotificationService constructor.
     *
     * @param ClientRepositoryInterface            $clientRepository
     * @param ExtractionContactRepositoryInterface $extractionContactRepository
     * @param ClientNotificationFactoryInterface   $clientNotificationFactory
     * @param MailerGdprInterface                  $mailerGdpr
     */
    public function __construct(
        ClientRepositoryInterface            $clientRepository,
        ExtractionContactRepositoryInterface $extractionContactRepository,
        ClientNotificationFactoryInterface   $clientNotificationFactory,
        MailerGdprInterface                  $mailerGdpr
    ) {
        $this->clientRepository             = $clientRepository;
        $this->extractionContactRepository  = $extractionContactRepository;
        $this->clientNotificationFactory    = $clientNotificationFactory;
        $this->mailerGdpr                   = $mailerGdpr;
    }

    /**
     * {@inheritdoc}
     */
    public function notifyClientsToRemoveLeads(array $leads): void
    {
        foreach ($leads as $lead) {
            $this->notifyClientsToRemoveLead($lead);
        }
    }

    /**
     * Notify each client about a specific contact that requested the right to be forgotten
     *
     * @param $lead
     */
    private function notifyClientsToRemoveLead(Lead $lead): void
    {
        $clientsData = $this->extractionContactRepository->getLeadCampaignsGroupByClient($lead);

        foreach ($clientsData as $clientData) {
            $client = $this->clientRepository->getById($clientData[static::CLIENT_ID]);

            $this->notifyClientToRemoveLead(
                $lead,
                $client,
                $clientData[static::PHONE_KEY],
                $clientData[static::EMAILS_KEY],
                $clientData[static::CAMPAIGNS_NAMES_KEY],
                $clientData[static::CAMPAIGNS_IDS_KEY]
            );
        }
    }

    /**
     * Notify a client about the contact that requested the right to be forgotten
     *
     * @param Lead   $lead
     * @param Client $client
     * @param string $phone
     * @param string $emails
     * @param string $campaignsNames
     * @param string $campaignsIds
     */
    private function notifyClientToRemoveLead(
        Lead $lead,
        Client $client,
        string $phone,
        string $emails,
        string $campaignsNames,
        string $campaignsIds
    ): void
    {
        $contentHtml = 'Contact Information: <br />';
        $contentHtml .= sprintf('Phone: %s <br />', $phone);
        $contentHtml .= sprintf('Email(s): %s <br />', $emails);
        $contentHtml .= sprintf('Campaign(s): %s <br />', $campaignsNames);

        try {
            $this->mailerGdpr->sendNotificationToForgetContact(
                [$client->getEmailAddress()],
                static::REMOVE_LEAD_NOTIFICATION_SUBJECT,
                [
                    'contentHtml' => $contentHtml
                ]
            );

            $clientNotification = $this->clientNotificationFactory->create(
                $client,
                $lead,
                ClientNotification::TYPE_RIGHT_TO_BE_FORGOTTEN,
                $campaignsIds
            );

            $this->entityManager->persist($clientNotification);
            $this->entityManager->flush();

        } catch (\Exception $exception) {
            $this->logger->error(
                static::NOTIFY_CLIENT_ERROR_MESSAGE,
                [
                    'message'   => $exception->getMessage(),
                    'client_id' => $client->getId(),
                    'lead_id'   => $lead->getId()
                ]
            );
        }
    }
}