<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

use ListBroking\AppBundle\Entity\Contact;
use ListBroking\AppBundle\Entity\Lead;
use ListBroking\AppBundle\Repository\ContactRepositoryInterface;
use ListBroking\AppBundle\Repository\LeadRepositoryInterface;

class LeadService implements LeadServiceInterface
{
    /**
     * @var LeadRepositoryInterface
     */
    private $leadRepository;

    /**
     * @var ContactRepositoryInterface
     */
    private $contactRepository;

    /**
     * LeadService constructor.
     *
     * @param LeadRepositoryInterface $leadRepository
     * @param ContactRepositoryInterface $contactRepository
     */
    public function __construct(LeadRepositoryInterface $leadRepository, ContactRepositoryInterface $contactRepository)
    {
        $this->leadRepository       = $leadRepository;
        $this->contactRepository    = $contactRepository;
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
     * Check if a specific Lead is in a list of Leads
     *
     * @param Lead  $lead
     * @param array $list
     *
     * @return bool
     */
    private function isLeadInList (Lead $lead, array $list): bool
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