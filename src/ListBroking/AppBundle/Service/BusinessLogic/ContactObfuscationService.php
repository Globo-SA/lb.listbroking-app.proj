<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\ExtractionDeduplication;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Entity\OppositionList;
use ListBroking\AppBundle\Repository\ExtractionDeduplicationRepositoryInterface;
use ListBroking\AppBundle\Repository\OppositionListRepositoryInterface;
use ListBroking\AppBundle\Repository\StagingContactDQPRepositoryInterface;
use ListBroking\AppBundle\Repository\StagingContactProcessedRepositoryInterface;
use ListBroking\AppBundle\Repository\StagingContactRepositoryInterface;
use ListBroking\AppBundle\Service\Base\BaseService;

class ContactObfuscationService extends BaseService implements ContactObfuscationServiceInterface
{
    private const OBFUSCATION_ENCRYPTION_ALGORITHM             = 'sha256';
    private const ERASE_CONTACT_ERROR_MESSAGE                  = '#LB-0041# Unable to erase contact';
    private const OBFUSCATING_CONTACT_MESSAGE                  = 'Obfuscating Contact';
    private const OBFUSCATING_LEAD_MESSAGE                     = 'Obfuscating Lead';
    private const OBFUSCATING_EXTRACTION_DEDUPLICATION_MESSAGE = 'Obfuscating ExtractionDeduplication';
    private const OBFUSCATING_OPPOSITION_LIST_MESSAGE          = 'Obfuscating OppositionList';
    private const DELETING_STAGING_CONTACTS_MESSAGE            = 'Deleting staging contacts';

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
     * @var string
     */
    private $emailInvalidDomain;

    /**
     * ContactObfuscationService constructor.
     *
     * @param ExtractionDeduplicationRepositoryInterface $extractionDeduplicationRepository
     * @param OppositionListRepositoryInterface          $oppositionListRepository
     * @param StagingContactRepositoryInterface          $stagingContactRepository
     * @param StagingContactProcessedRepositoryInterface $stagingContactProcessedRepository
     * @param StagingContactDQPRepositoryInterface       $stagingContactDQPRepository
     * @param string                                     $emailInvalidDomain
     */
    public function __construct(
        ExtractionDeduplicationRepositoryInterface $extractionDeduplicationRepository,
        OppositionListRepositoryInterface $oppositionListRepository,
        StagingContactRepositoryInterface $stagingContactRepository,
        StagingContactProcessedRepositoryInterface $stagingContactProcessedRepository,
        StagingContactDQPRepositoryInterface $stagingContactDQPRepository,
        string $emailInvalidDomain
    ) {
        $this->extractionDeduplicationRepository = $extractionDeduplicationRepository;
        $this->oppositionListRepository          = $oppositionListRepository;
        $this->stagingContactRepository          = $stagingContactRepository;
        $this->stagingContactProcessedRepository = $stagingContactProcessedRepository;
        $this->stagingContactDQPRepository       = $stagingContactDQPRepository;
        $this->emailInvalidDomain                = $emailInvalidDomain;
    }

    /**
     * {@inheritdoc}
     */
    public function isPhoneObfuscatedInOppositionList(string $phone): bool
    {
        $obfuscatedPhone = $this->encrypt($phone);

        return $this->oppositionListRepository->isPhoneInOppositionList($obfuscatedPhone);
    }

    /**
     * {@inheritdoc}
     */
    public function obfuscateAllContactData(array $leads, string $email, string $phone): bool
    {
        try {
            $this->entityManager->beginTransaction();

            $this->obfuscateByLeads($leads);
            $this->obfuscateByEmailOrPhone($email, $phone);

            $this->entityManager->flush();
            $this->entityManager->commit();

            return true;

        } catch (\Exception $exception) {
            $this->logger->critical(static::ERASE_CONTACT_ERROR_MESSAGE, ['message' => $exception->getMessage()]);
            $this->entityManager->rollback();

            return false;
        }
    }

    /**
     * Obfuscate data from a collection of Leads
     *
     * @param Lead[] $leads
     *
     * @return void
     */
    private function obfuscateByLeads(array $leads): void
    {
        foreach ($leads as $lead) {
            $this->obfuscateByLead($lead);
        }
    }

    /**
     * Obfuscate data from a given email or phone
     *
     * @param string $email
     * @param string $phone
     *
     * @return void
     */
    private function obfuscateByEmailOrPhone(string $email, string $phone): void
    {
        $extractionDeduplications = $this->extractionDeduplicationRepository->getByPhone($phone);
        $oppositionLists          = $this->oppositionListRepository->getByPhone($phone);

        $this->obfuscateExtractionDeduplications($extractionDeduplications);
        $this->obfuscateOppositionLists($oppositionLists);
        $this->deleteAllStagingContactByEmailOrPhone($email, $phone);
    }

    /**
     * Obfuscate data from a given Lead
     *
     * @param Lead $lead
     *
     * @return void
     */
    private function obfuscateByLead(Lead $lead): void
    {
        foreach ($lead->getContacts() as $contact) {
            $this->obfuscateByLeadAndContact($lead, $contact);
        }

        $this->obfuscateLead($lead);
    }

    /**
     * Obfuscate data from a given Lead and Contact
     *
     * @param Lead    $lead
     * @param Contact $contact
     *
     * @return void
     */
    private function obfuscateByLeadAndContact(Lead $lead, Contact $contact): void
    {
        $phone = $lead->getPhone();
        $email = $contact->getEmail();

        $extractionDeduplications = $this->extractionDeduplicationRepository->getByContactIdOrLeadId(
            $contact->getId(),
            $lead->getId()
        );

        $oppositionLists = $this->oppositionListRepository->getByPhone($phone);

        $this->obfuscateContact($contact);
        $this->obfuscateExtractionDeduplications($extractionDeduplications);
        $this->obfuscateOppositionLists($oppositionLists);
        $this->deleteAllStagingContactByEmailOrPhone($email, $phone);
    }

    /**
     * Obfuscate a specific Contact
     *
     * @param Contact $contact
     *
     * @return void
     */
    private function obfuscateContact(Contact $contact): void
    {
        $obfuscatedEmail       = $this->encryptEmail($contact->getEmail());
        $obfuscatedFirstName   = $this->encrypt($contact->getFirstname());
        $obfuscatedLastName    = $this->encrypt($contact->getFirstname());
        $obfuscatedPostRequest = $this->encrypt(json_encode($contact->getPostRequest()));

        $contact->setEmail($obfuscatedEmail);
        $contact->setFirstname($obfuscatedFirstName);
        $contact->setLastname($obfuscatedLastName);
        $contact->setPostRequest([$obfuscatedPostRequest]);

        $this->entityManager->persist($contact);

        $this->logger->info(static::OBFUSCATING_CONTACT_MESSAGE, ['contactId' => $contact->getId()]);
    }

    /**
     * Obfuscate a specific Lead
     *
     * @param Lead $lead
     *
     * @return void
     */
    private function obfuscateLead(Lead $lead): void
    {
        $obfuscatedPhone = $this->encrypt($lead->getPhone());

        $lead->setPhone($obfuscatedPhone);
        $lead->setInOpposition(true);
        $lead->setIsReadyToUse(false);

        $this->entityManager->persist($lead);

        $this->logger->info(static::OBFUSCATING_LEAD_MESSAGE, ['leadId' => $lead->getId()]);
    }

    /**
     * Obfuscate a collection of ExtractionDeduplications
     *
     * @param ExtractionDeduplication[] $extractionDeduplications
     *
     * @return void
     */
    private function obfuscateExtractionDeduplications(array $extractionDeduplications): void
    {
        foreach ($extractionDeduplications as $extractionDeduplication){
            $this->obfuscateExtractionDeduplication($extractionDeduplication);
        }
    }

    /**
     * Obfuscate a specific ExtractionDeduplication
     *
     * @param ExtractionDeduplication $extractionDeduplication
     *
     * @return void
     */
    private function obfuscateExtractionDeduplication(ExtractionDeduplication $extractionDeduplication): void
    {
        $obfuscatedPhone = $this->encrypt($extractionDeduplication->getPhone());

        $extractionDeduplication->setPhone($obfuscatedPhone);

        $this->entityManager->persist($extractionDeduplication);

        $this->logger->info(
            static::OBFUSCATING_EXTRACTION_DEDUPLICATION_MESSAGE,
            ['extractionDeduplicationId' => $extractionDeduplication->getId()]
        );
    }

    /**
     * Obfuscate a collection a OppositionLists
     *
     * @param OppositionList[] $oppositionLists
     *
     * @return void
     */
    private function obfuscateOppositionLists(array $oppositionLists): void
    {
        foreach ($oppositionLists as $oppositionList){
            $this->obfuscateOppositionList($oppositionList);
        }
    }

    /**
     * Obfuscate a specific OppositionList
     *
     * @param OppositionList $oppositionList
     *
     * @return void
     */
    private function obfuscateOppositionList(OppositionList $oppositionList): void
    {
        $obfuscatedPhone = $this->encrypt($oppositionList->getPhone());

        $oppositionList->setPhone($obfuscatedPhone);

        $this->entityManager->persist($oppositionList);

        $this->logger->info(
            static::OBFUSCATING_OPPOSITION_LIST_MESSAGE,
            ['oppositionListId' => $oppositionList->getId()]
        );
    }

    /**
     * Deletes all entries from stagingContacts
     *
     * @param string $email
     * @param string $phone
     *
     * @return void
     */
    private function deleteAllStagingContactByEmailOrPhone(string $email, string $phone): void
    {
        $this->stagingContactDQPRepository->deleteContactByEmailOrPhone($email, $phone);
        $this->stagingContactProcessedRepository->deleteContactByEmailOrPhone($email, $phone);
        $this->stagingContactRepository->deleteContactByEmailOrPhone($email, $phone);

        $this->logger->info(static::DELETING_STAGING_CONTACTS_MESSAGE);
    }

    /**
     * Encrypts a given email
     *
     * @param string $email
     *
     * @return string
     */
    private function encryptEmail(string $email): string
    {
        return sprintf('%s@%s', $this->encrypt($email), $this->emailInvalidDomain);
    }

    /**
     * Encrypts a given data
     *
     * @param mixed $data
     *
     * @return string
     */
    private function encrypt($data): string
    {
        return hash(static::OBFUSCATION_ENCRYPTION_ALGORITHM, $data);
    }
}