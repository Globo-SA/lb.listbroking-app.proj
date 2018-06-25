<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\ExtractionDeduplication;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Entity\OppositionList;
use ListBroking\AppBundle\Repository\ContactRepositoryInterface;
use ListBroking\AppBundle\Repository\LeadRepositoryInterface;
use ListBroking\AppBundle\Repository\ExtractionDeduplicationRepositoryInterface;
use ListBroking\AppBundle\Repository\OppositionListRepositoryInterface;
use ListBroking\AppBundle\Repository\StagingContactDQPRepositoryInterface;
use ListBroking\AppBundle\Repository\StagingContactProcessedRepositoryInterface;
use ListBroking\AppBundle\Repository\StagingContactRepositoryInterface;
use ListBroking\AppBundle\Service\Base\BaseService;

class ContactObfuscationService extends BaseService implements ContactObfuscationServiceInterface
{
    const OBFUSCATION_ENCRYPTION_ALGORITHM      = 'sha256';
    const EMAIL_NOT_FOUND_MESSAGE               = 'Email Not found';
    const PHONE_NOT_FOUND_MESSAGE               = 'Phone Not found';
    const NOT_FOUND_ERROR_CODE                  = 404;
    const EMAIL_ALREADY_OBFUSCATED_MESSAGE      = 'Email Already erased';
    const PHONE_ALREADY_OBFUSCATED_MESSAGE      = 'Phone Already erased';
    const ALREADY_FORGOTTEN_ERROR_CODE          = 400;
    const ERROR_MESSAGE_FORMAT                  = '%s: %s';

    /**
     * @var ContactRepositoryInterface
     */
    private $contactRepository;

    /**
     * @var LeadRepositoryInterface
     */
    private $leadRepository;

    /**
     * @var ExtractionDeduplicationRepositoryInterface
     */
    private $extractionDeduplicationRepository;

    /**
     * @var OppositionListRepositoryInterface
     */
    private $oppositionListRepository;

    /**
     * @var StagingContactRepositoryInterface
     */
    private $stagingContactRepository;

    /**
     * @var StagingContactProcessedRepositoryInterface
     */
    private $stagingContactProcessedRepository;

    /**
     * @var StagingContactDQPRepositoryInterface
     */
    private $stagingContactDQPRepository;

    /**
     * @var ClientNotificationServiceInterface
     */
    private $clientNotificationService;

    /**
     * @var string
     */
    private $emailInvalidDomain;

    /**
     * ContactObfuscationService constructor.
     *
     * @param ContactRepositoryInterface $contactRepository
     * @param LeadRepositoryInterface $leadRepository
     * @param ExtractionDeduplicationRepositoryInterface $extractionDeduplicationRepository
     * @param OppositionListRepositoryInterface $oppositionListRepository
     * @param StagingContactRepositoryInterface $stagingContactRepository
     * @param StagingContactProcessedRepositoryInterface $stagingContactProcessedRepository
     * @param StagingContactDQPRepositoryInterface $stagingContactDQPRepository
     * @param ClientNotificationServiceInterface $clientNotificationService
     * @param string $emailInvalidDomain
     */
    public function __construct(
        ContactRepositoryInterface $contactRepository,
        LeadRepositoryInterface $leadRepository,
        ExtractionDeduplicationRepositoryInterface $extractionDeduplicationRepository,
        OppositionListRepositoryInterface $oppositionListRepository,
        StagingContactRepositoryInterface $stagingContactRepository,
        StagingContactProcessedRepositoryInterface $stagingContactProcessedRepository,
        StagingContactDQPRepositoryInterface $stagingContactDQPRepository,
        ClientNotificationServiceInterface $clientNotificationService,
        string $emailInvalidDomain
    ) {
        $this->contactRepository                    = $contactRepository;
        $this->leadRepository                       = $leadRepository;
        $this->extractionDeduplicationRepository    = $extractionDeduplicationRepository;
        $this->oppositionListRepository             = $oppositionListRepository;
        $this->stagingContactRepository             = $stagingContactRepository;
        $this->stagingContactProcessedRepository    = $stagingContactProcessedRepository;
        $this->stagingContactDQPRepository          = $stagingContactDQPRepository;
        $this->clientNotificationService            = $clientNotificationService;
        $this->emailInvalidDomain                   = $emailInvalidDomain;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function obfuscateContactByEmail(string $email, bool $notifyClient = true): void
    {
        if ($email == ''){
            return;
        }

        $contact = $this->findContactByEmail($email);

        $lead = $contact->getLead();
        if (!($lead instanceof Lead)) {
            throw new \Exception(static::EMAIL_NOT_FOUND_MESSAGE, static::NOT_FOUND_ERROR_CODE);
        }

        // Notify clients before obfuscating the contact
        if ($notifyClient === true){
            $this->clientNotificationService->notifyClientToObfuscateLead($lead);
        }

        $this->obfuscate($lead);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function obfuscateContactByPhone(string $phone, bool $notifyClient = true): void
    {
        if ($phone == ''){
            return;
        }

        $lead = $this->findLeadByPhone($phone);

        // Notify clients before obfuscating the contact
        if ($notifyClient === true){
            $this->clientNotificationService->notifyClientToObfuscateLead($lead);
        }

        $this->obfuscate($lead);
    }

    /**
     * @param string $email
     *
     * @return Contact
     * @throws \Exception
     */
    private function findContactByEmail(string $email): Contact
    {
        $contact = $this->contactRepository->findContactByEmail($email);

        if (!($contact instanceof Contact)) {
            $encryptedEmail = $this->encryptEmail($email);

            $contact = $this->contactRepository->findContactByEmail($encryptedEmail);
            if (!($contact instanceof Contact)) {
                throw new \Exception(
                    sprintf(static::ERROR_MESSAGE_FORMAT, static::EMAIL_NOT_FOUND_MESSAGE, $encryptedEmail),
                    static::NOT_FOUND_ERROR_CODE
                );
            }

            throw new \Exception(
                sprintf(static::ERROR_MESSAGE_FORMAT, static::EMAIL_ALREADY_OBFUSCATED_MESSAGE, $encryptedEmail),
                static::ALREADY_FORGOTTEN_ERROR_CODE
            );
        }

        return $contact;
    }

    /**
     * @param string $phone
     *
     * @return Lead
     * @throws \Exception
     */
    private function findLeadByPhone(string $phone): Lead
    {
        $lead = $this->leadRepository->findLeadByPhone($phone);

        if (!($lead instanceof Lead)) {
            $encryptedPhone = $this->encrypt($phone);

            $lead = $this->leadRepository->findLeadByPhone($encryptedPhone);
            if (!($lead instanceof Lead)) {
                throw new \Exception(
                    sprintf(static::ERROR_MESSAGE_FORMAT, static::PHONE_NOT_FOUND_MESSAGE, $encryptedPhone),
                    static::NOT_FOUND_ERROR_CODE
                );
            }

            throw new \Exception(
                sprintf(static::ERROR_MESSAGE_FORMAT, static::PHONE_ALREADY_OBFUSCATED_MESSAGE, $encryptedPhone),
                static::ALREADY_FORGOTTEN_ERROR_CODE
            );
        }

        return $lead;
    }

    /**
     * Obfuscates all information about the Lead
     *
     * @param Lead $lead
     *
     * @return Lead
     */
    private function obfuscate(Lead $lead): Lead
    {
        $phone = $lead->getPhone();

        /** @var Contact $contact */
        foreach ($lead->getContacts() as $contact){
            $email = $contact->getEmail();

            $this->obfuscateContact($contact);
            $this->obfuscateExtractionDeduplications($contact, $lead);
            $this->obfuscateOppositionList($phone);
            $this->stagingContactDQPRepository->deleteContactByEmailOrPhone($email, $phone);
            $this->stagingContactProcessedRepository->deleteContactByEmailOrPhone($email, $phone);
            $this->stagingContactRepository->deleteContactByEmailOrPhone($email, $phone);
        }

        $this->obfuscateLead($lead);

        return $lead;
    }

    /**
     * @param Contact $contact
     */
    private function obfuscateContact(Contact $contact)
    {
        $obfuscatedEmail       = $this->encryptEmail($contact->getEmail());
        $obfuscatedFirstName   = $this->encrypt($contact->getFirstname());
        $obfuscatedLastName    = $this->encrypt($contact->getFirstname());
        $obfuscatedPostRequest = $this->encrypt(json_encode($contact->getPostRequest()));

        $contact->setEmail($obfuscatedEmail);
        $contact->setFirstname($obfuscatedFirstName);
        $contact->setLastname($obfuscatedLastName);
        $contact->setPostRequest([$obfuscatedPostRequest]);

        $this->updateEntity($contact);
    }

    /**
     * @param Lead $lead
     */
    private function obfuscateLead(Lead $lead)
    {
        $obfuscatedPhone = $this->encrypt($lead->getPhone());

        $lead->setPhone($obfuscatedPhone);
        $lead->setInOpposition(true);
        $lead->setIsReadyToUse(false);

        $this->updateEntity($lead);
    }

    /**
     * @param Contact $contact
     * @param Lead $lead
     */
    private function obfuscateExtractionDeduplications(Contact $contact, Lead $lead)
    {
        $extractionDeduplications = $this->extractionDeduplicationRepository->findByContactIdOrLeadId(
            $contact->getId(),
            $lead->getId()
        );

        /** @var ExtractionDeduplication $extractionDeduplication */
        foreach ($extractionDeduplications as $extractionDeduplication){
            $obfuscatedPhone = $this->encrypt($extractionDeduplication->getPhone());

            $extractionDeduplication->setPhone($obfuscatedPhone);

            $this->updateEntity($extractionDeduplication);
        }
    }

    /**
     * @param string $phone
     */
    private function obfuscateOppositionList(string $phone)
    {
        $obfuscatedPhone = $this->encrypt($phone);
        $oppositionLists = $this->oppositionListRepository->findByPhone($phone);

        /** @var OppositionList $oppositionList */
        foreach ($oppositionLists as $oppositionList){
            $oppositionList->setPhone($obfuscatedPhone);
            $this->updateEntity($oppositionList);
        }
    }

    /**
     * @param string $email
     *
     * @return string
     */
    private function encryptEmail(string $email)
    {
        return sprintf('%s@%s', $this->encrypt($email), $this->emailInvalidDomain);
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    private function encrypt($data)
    {
        return hash(static::OBFUSCATION_ENCRYPTION_ALGORITHM, $data);
    }
}