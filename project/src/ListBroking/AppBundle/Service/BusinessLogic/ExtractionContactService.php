<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Repository\ExtractionContactRepositoryInterface;

class ExtractionContactService implements ExtractionContactServiceInterface
{
    /**
     * @var ExtractionContactRepositoryInterface
     */
    private $extractionContactRepository;

    /**
     * ExtractionContactService constructor.
     *
     * @param ExtractionContactRepositoryInterface $extractionContactRepository
     */
    public function __construct(ExtractionContactRepositoryInterface $extractionContactRepository)
    {
        $this->extractionContactRepository = $extractionContactRepository;
    }

    /**
     * @param Contact $contact
     *
     * @return array
     */
    public function findContactExtractions(Contact $contact): array
    {
        return $this->extractionContactRepository->findContactExtractions($contact);
    }

    /**
     * {@inheritdoc}
     */
    public function getContactHistoryByLead(Lead $lead, bool $sold = true): array
    {
        return $this->extractionContactRepository->getExtractionContactsSoldByLead($lead, $sold);
    }
}
