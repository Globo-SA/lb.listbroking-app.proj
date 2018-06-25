<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use Adclick\Components\GDPR\Mailer\MailerGdpr;
use ListBroking\AppBundle\Entity\Campaign;
use ListBroking\AppBundle\Entity\Client;
use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\ExtractionContact;
use ListBroking\AppBundle\Entity\Lead;

class ClientNotificationService implements ClientNotificationServiceInterface
{
    const OBFUSCATE_LEAD_NOTIFICATION_SUBJECT = 'Right to be forgotten request';

    /**
     * @var MailerGdpr
     */
    private $mailer;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $mailerToMail;

    /**
     * ClientNotificationService constructor.
     * @param MailerGdpr $mailer
     * @param string $environment
     * @param string $mailerToMail
     */
    public function __construct(MailerGdpr $mailer, string $environment, string $mailerToMail)
    {
        $this->mailer       = $mailer;
        $this->environment  = $environment;
        $this->mailerToMail = $mailerToMail;
    }

    /**
     * {@inheritdoc}
     */
    public function notifyClientToObfuscateLead(Lead $lead)
    {
        $toEmails = [];

        $contentHtml = 'Contact Information <br />';
        $contentHtml .= sprintf("Phone: %s <br />", $lead->getPhone());

        /** @var Contact $contact */
        foreach ($lead->getContacts() as $contact) {
            $contentHtml .= sprintf("Email: %s <br />", $contact->getEmail());

            /** @var ExtractionContact $extractionContact */
            foreach ($contact->getExtractionContacts() as $extractionContact) {
                $extraction = $extractionContact->getExtraction();

                /** @var Campaign $campaign */
                $campaign = $extraction->getCampaign();
                $contentHtml .= sprintf("Campaign: %s <br />", $campaign->getName());

                /** @var Client $client */
                $client = $campaign->getClient();
                $contentHtml .= sprintf("Client: %s <br />", $client->getName());

                $toEmails[] = $client->getEmailAddress();
            }
        }

        // To prevent sending notifications to clients in dev and staging environments
        if ($this->environment !== 'prod'){
            $toEmails = [$this->mailerToMail];
        }

        if (count($toEmails) > 0){
            $this->mailer->sendNotificationToForgetContact(
                $toEmails,
                static::OBFUSCATE_LEAD_NOTIFICATION_SUBJECT,
                [
                    'contentHtml' => $contentHtml
                ]
            );
        }
    }
}