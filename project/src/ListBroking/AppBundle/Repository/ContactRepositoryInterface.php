<?php

namespace ListBroking\AppBundle\Repository;

interface ContactRepositoryInterface
{
    /**
     * @param string $email
     *
     * @return array
     */
    public function findByEmail(string $email): array;

    /**
     * @param     $id
     * @param int $leadId
     *
     * @return mixed
     */
    public function findByIdAndLead($id, $leadId);

    public function findByLeadAndEmailAndOwner($leadId, $email, $owner);
}
