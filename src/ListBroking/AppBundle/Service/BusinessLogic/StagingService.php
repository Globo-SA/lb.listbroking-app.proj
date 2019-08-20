<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Engine\ValidatorEngine;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Entity\OppositionList;
use ListBroking\AppBundle\Entity\StagingContact;
use ListBroking\AppBundle\Enum\ErrorCodesEnum;
use ListBroking\AppBundle\Exception\Validation\OppositionListException;
use ListBroking\AppBundle\Repository\LeadRepository;
use ListBroking\AppBundle\Repository\OppositionListRepository;
use ListBroking\AppBundle\Repository\SourceRepositoryInterface;
use ListBroking\AppBundle\Repository\StagingContactRepository;
use ListBroking\AppBundle\Service\Base\BaseService;
use ListBroking\AppBundle\Service\Factory\OppositionListFactory;
use Symfony\Component\Validator\Validator\RecursiveValidator;

/**
 * ListBroking\AppBundle\Service\BusinessLogic\StagingService
 */
class StagingService extends BaseService implements StagingServiceInterface
{
    /**
     * @var ValidatorEngine
     */
    protected $validatorEngine;

    /**
     * @var OppositionListFactory $oppositionListFactory
     */
    protected $oppositionListFactory;

    /**
     * @var RecursiveValidator $validator
     */
    protected $validator;

    /**
     * @var OppositionListRepository $oppositionListRepository
     */
    protected $oppositionListRepository;

    /**
     * @var StagingContactRepository $stagingContactRepository
     */
    protected $stagingContactRepository;

    /**
     * @var LeadRepository $leadRepository
     */
    protected $leadRepository;

    /**
     * @var SourceRepositoryInterface
     */
    protected $sourceRepository;

    /**
     * @var ContactObfuscationServiceInterface
     */
    protected $contactObfuscationService;

    /**
     * StagingService constructor.
     *
     * @param ValidatorEngine $validatorEngine
     * @param OppositionListFactory $oppositionListFactory
     * @param RecursiveValidator $validator
     * @param OppositionListRepository $oppositionListRepository
     * @param StagingContactRepository $stagingContactRepository
     * @param LeadRepository $leadRepository ,
     * @param SourceRepositoryInterface $sourceRepository
     * @param ContactObfuscationServiceInterface $contactObfuscationService
     */
    public function __construct(
        ValidatorEngine $validatorEngine,
        OppositionListFactory $oppositionListFactory,
        RecursiveValidator $validator,
        OppositionListRepository $oppositionListRepository,
        StagingContactRepository $stagingContactRepository,
        LeadRepository $leadRepository,
        SourceRepositoryInterface $sourceRepository,
        ContactObfuscationServiceInterface $contactObfuscationService
    ) {
        $this->validatorEngine           = $validatorEngine;
        $this->oppositionListFactory     = $oppositionListFactory;
        $this->validator                 = $validator;
        $this->oppositionListRepository  = $oppositionListRepository;
        $this->stagingContactRepository  = $stagingContactRepository;
        $this->leadRepository            = $leadRepository;
        $this->sourceRepository          = $sourceRepository;
        $this->contactObfuscationService = $contactObfuscationService;
    }

    /**
     * {@inheritdoc}
     */
    public function addStagingContact(array $dataArray)
    {
        $stagingContact = $this->stagingContactRepository->addStagingContact($dataArray);

        if (
            $this->oppositionListRepository->isPhoneInOppositionList($dataArray[Lead::PHONE_KEY]) ||
            $this->contactObfuscationService->isPhoneObfuscatedInOppositionList($dataArray[Lead::PHONE_KEY])
        ) {
            $stagingContact->setInOpposition(true);
        }

        return $stagingContact;
    }

    /**
     * {@inheritdoc}
     */
    public function findAndLockContactsToValidate($limit = 50)
    {
        return $this->stagingContactRepository->findAndLockContactsToValidate($limit);
    }

    /**
     * {@inheritdoc}
     */
    public function importOppositionList($type, \PHPExcel $file)
    {
        $config = $this->findConfig('opposition_list.config');

        $this->oppositionListRepository->importOppositionListFile($type, $config[$type], $file);
    }

    /**
     * {@inheritdoc}
     */
    public function addPhoneToOppositionList(string $type, string $phone): OppositionList
    {
        $this->entityManager->beginTransaction();
        try{
            $opposition = $this->createOpposition($type, $phone);
            $leadsUpdated = $this->leadRepository->updateInOppositionByPhone($opposition->getPhone(), true);
            $this->logger->info(
                sprintf('%s leads marked as in opposition list with phone %s', $leadsUpdated, $phone)
            );
            $this->entityManager->commit();
        } catch (\Exception $exception){
            $this->entityManager->rollback();
            throw new OppositionListException();
        }

        return $opposition;
    }

    /**
     * {@inheritdoc}
     */
    public function importStagingContacts(\PHPExcel $file, $batchSize, array $extraFields = [])
    {
        $this->stagingContactRepository->importStagingContactsFile($file, $extraFields, $batchSize);
    }

    /**
     * {@inheritdoc}
     */
    public function loadValidatedContact(StagingContact $stagingContact)
    {
        $dimensions = $this->loadStagingContactDimensions($stagingContact);
        $this->stagingContactRepository->loadValidatedContact($stagingContact, $dimensions);
    }

    /**
     * {@inheritdoc}
     */
    public function moveInvalidContactsToDQP($limit)
    {
        $this->stagingContactRepository->moveInvalidContactsToDQP($limit);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUpdatedContact(StagingContact $stagingContact)
    {
        $this->logger->debug(sprintf('Loading Staging Contact %s for Update', $stagingContact->getId()));

        $dimensions = $this->loadStagingContactDimensions($stagingContact);
        if (!empty($stagingContact->getContactId())) {
            $this->logger->warning(ErrorCodesEnum::WARNING_MESSAGE_COULD_NOT_LOAD_CONTACT);

            return;
        }

        $this->logger->debug(
            sprintf(
                'Updating Contact %s with Staging Contact %s',
                $stagingContact->getContactId(),
                $stagingContact->getId()
            )
        );

        $contact = $this->stagingContactRepository->getNotCleanedContactById($stagingContact);
        if (!$contact) {
            $this->stagingContactRepository->moveStagingContactToDQP($stagingContact);

            $this->logger->error(ErrorCodesEnum::ERROR_MESSAGE_CONTACT_NOT_FOUND_STAGING_PROCESS);

            return;
        }

        $this->logger->debug(sprintf('Update Contact %s Facts', $stagingContact->getContactId()));
        $contact->updateContactFacts($stagingContact);

        $this->logger->debug(sprintf('Update Contact %s Dimensions', $stagingContact->getContactId()));
        $contact->updateContactDimensions(array_values($dimensions));

        $contact->setIsClean(true);

        $this->logger->debug(sprintf('Move Staging Contact %s to Processed', $stagingContact->getId()));
        $this->stagingContactRepository->moveStagingContactToProcessed($stagingContact);
    }

    /**
     * {@inheritdoc}
     */
    public function syncContactsWithOppositionLists()
    {
        $this->leadRepository->syncLeadsWithOppositionLists();
    }

    /**
     * {@inheritdoc}
     */
    public function validateStagingContact(StagingContact $stagingContact)
    {
        $this->logger->debug(sprintf('Validate Staging Contact %s', $stagingContact->getId()));

        $this->validatorEngine->run($stagingContact);
    }

    /**
     * {@inheritdoc}
     */
    public function findLeadsWithExpiredInitialLock($limit)
    {
        return $this->leadRepository->findLeadsWithExpiredInitialLock($limit);
    }

    /**
     * Get staging contact dimensions as an array
     *
     * @param StagingContact $stagingContact
     *
     * @return array
     */
    private function loadStagingContactDimensions(StagingContact $stagingContact)
    {
        $this->logger->debug(sprintf('Loading Dimensions for contact %s', $stagingContact->getId()));

        $source = $this->sourceRepository->getByExternalId($stagingContact->getSourceExternalId());

        //Dimension Tables
        return [
            'source'       => $source,
            'owner'        => $this->findDimension('ListBrokingAppBundle:Owner', $stagingContact->getOwner()),
            'sub_category' => $this->findDimension(
                'ListBrokingAppBundle:SubCategory',
                $stagingContact->getSubCategory()
            ),
            'gender'       => $this->findDimension('ListBrokingAppBundle:Gender', $stagingContact->getGender()),
            'district'     => $this->findDimension('ListBrokingAppBundle:District', $stagingContact->getDistrict()),
            'county'       => $this->findDimension('ListBrokingAppBundle:County', $stagingContact->getCounty()),
            'parish'       => $this->findDimension('ListBrokingAppBundle:Parish', $stagingContact->getParish()),
            'country'      => $this->findDimension('ListBrokingAppBundle:Country', $stagingContact->getCountry()),
        ];
    }

    /**
     * Finds the facts table Dimensions by name
     *
     * @param string $repoName
     * @param string $name
     *
     * @return null|object
     */
    private function findDimension($repoName, $name)
    {
        $this->logger->debug(sprintf('Find %s with name %s', $repoName, $name));

        return $this->entityManager->getRepository($repoName)
                                   ->findOneBy(['name' => $name]);
    }

    /**
     * @param string $type
     * @param string $phone
     *
     * @return OppositionList
     */
    private function createOpposition(string $type, string $phone): OppositionList
    {
        $opposition = $this->oppositionListFactory->create($type, $phone);
        $errors = $this->validator->validate($opposition);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->logger->info($error);
            }

            throw new OppositionListException();
        }

        $this->entityManager->persist($opposition);
        $this->entityManager->flush();

        return $opposition;
    }
}
