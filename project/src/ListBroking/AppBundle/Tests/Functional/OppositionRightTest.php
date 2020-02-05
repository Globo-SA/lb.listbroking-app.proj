<?php

namespace ListBroking\Tests\AppBundle\Functional;

use ListBroking\AppBundle\Entity\Extraction;
use ListBroking\AppBundle\Entity\ExtractionContact;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Exception\Validation\OppositionListException;
use ListBroking\AppBundle\Repository\LeadRepository;
use ListBroking\AppBundle\Repository\StagingContactRepository;
use ListBroking\AppBundle\Service\BusinessLogic\ExtractionServiceInterface;
use ListBroking\AppBundle\Service\BusinessLogic\StagingServiceInterface;
use ListBroking\AppBundle\Service\Factory\LeadFactory;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class OppositionRightTest
 */
class OppositionRightTest extends KernelTestCase
{

    /**
     * @var StagingServiceInterface $stagingService
     */
    private $stagingService;

    /**
     * @var StagingContactRepository
     */
    private $stagingContactRepository;

    /**
     * @var LeadRepository $leadRepository
     */
    private $leadRepository;

    /**
     * @var LeadFactory $leadFactory
     */
    private $leadFactory;

    /**
     * @var ExtractionServiceInterface $extractionService
     */
    private $extractionService;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        static::bootKernel();
        $container                      = static::$kernel->getContainer();
        $this->stagingService           = $container->get('app.service.staging');
        $this->extractionService        = $container->get('app.service.extraction');
        $this->leadFactory              = $container->get('app.service.factory.lead');
        $this->stagingContactRepository = $container->get('app.repository.staging_contact');
        $this->leadRepository           = $container->get('app.repository.lead');
    }

    /**
     * Assert if a contact is marked as in opposition when it gets in the system,
     * if it is already on the opposition list
     */
    public function testInOppositionCheckWhenContactComesIn()
    {
        $stagingContact = $this->stagingService->addStagingContact(['phone' => 919999999]);
        Assert::assertTrue($stagingContact->getInOpposition());
    }

    /**
     * Asserts if a contact is marked as in opposition if is requested
     */
    public function testMarkedAsInOppositionWhenRequested()
    {

        $phone = 919191919;

        try {
            $this->stagingService->addPhoneToOppositionList('LEADCENTRE', $phone);
        } catch (OppositionListException $exception) {
        }

        $leads = $this->leadRepository->getByPhone($phone);

        array_map(
            function (Lead $lead) {
                Assert::assertTrue($lead->getInOpposition());
            },
            $leads
        );
    }


    /**
     * Asserts that a lead in opposition is not extracted
     */
    public function testInOppositionNotExtracted()
    {
        $extractions = $this->extractionService->findExtractionsByName('Test');

        array_map(
            function (Extraction $extraction) {
                Assert::assertTrue($this->extractionService->runExtraction($extraction));

                $extractionContacts = $this->extractionService->findExtractionContacts($extraction);

                array_map(
                    function (ExtractionContact $extractionContact) {
                        Assert::assertTrue(!$extractionContact->getContact()->getLead()->getInOpposition());
                    },
                    $extractionContacts
                );
            },
            $extractions
        );
    }
}
