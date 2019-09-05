<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Entity\LeadHist;
use ListBroking\AppBundle\Repository\ContactRepositoryInterface;
use ListBroking\AppBundle\Repository\LeadRepositoryInterface;

class LeadService implements LeadServiceInterface
{
    /**
     * @var LeadRepositoryInterface
     */
    private $leadRepository;

    /**
     * @var LeadRepositoryInterface
     */
    private $leadHistRepository;

    /**
     * @var ContactRepositoryInterface
     */
    private $contactRepository;

    /**
     * @var ContactRepositoryInterface
     */
    private $contactHistRepository;

    /**
     * LeadService constructor.
     *
     * @param LeadRepositoryInterface    $leadRepository
     * @param LeadRepositoryInterface    $leadHistRepository
     * @param ContactRepositoryInterface $contactRepository
     * @param ContactRepositoryInterface $contactHistRepository
     */
    public function __construct(
        LeadRepositoryInterface $leadRepository,
        LeadRepositoryInterface $leadHistRepository,
        ContactRepositoryInterface $contactRepository,
        ContactRepositoryInterface $contactHistRepository
    ) {
        $this->leadRepository        = $leadRepository;
        $this->leadHistRepository    = $leadHistRepository;
        $this->contactRepository     = $contactRepository;
        $this->contactHistRepository = $contactHistRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getLeads(string $email, string $phone): array
    {
        $leads    = $this->leadRepository->getByPhone($phone);
        $contacts = $this->contactRepository->findByEmail($email);

        /** @var Contact $contact */
        foreach ($contacts as $contact) {
            $contactLead = $contact->getLead();

            if ($this->isLeadInList($contactLead, $leads)) {
                continue;
            }

            $leads[] = $contactLead;
        }

        return $leads;
    }

    /**
     * {@inheritdoc}
     */
    public function getLeadsHist(string $email, string $phone): array
    {
        $leads    = $this->leadHistRepository->getByPhone($phone);
        $contacts = $this->contactHistRepository->findByEmail($email);

        /** @var Contact $contact */
        foreach ($contacts as $contact) {
            $contactLead = $contact->getLeadHist();

            if ($this->isLeadHistInList($contactLead, $leads)) {
                continue;
            }

            $leads[] = $contactLead;
        }

        return $leads;
    }

    /**
     * Check if a specific Lead is in a list of Leads
     *
     * @param Lead  $lead
     * @param array $list
     *
     * @return bool
     */
    private function isLeadInList(Lead $lead, array $list): bool
    {
        /** @var Lead $item */
        foreach ($list as $item) {
            if ($item->getId() === $lead->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a specific Lead is in a list of Leads
     *
     * @param LeadHist $lead
     * @param array    $list
     *
     * @return bool
     */
    private function isLeadHistInList(LeadHist $lead, array $list): bool
    {
        /** @var Lead $item */
        foreach ($list as $item) {
            if ($item->getId() === $lead->getId()) {
                return true;
            }
        }

        return false;
    }
}