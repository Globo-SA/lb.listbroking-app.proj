<?php

namespace ListBroking\AppBundle\Repository;

use ListBroking\AppBundle\Entity\Lead;

interface LeadRepositoryInterface
{
    /**
     * Synchronizes Leads with opposition lists
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function syncLeadsWithOppositionLists();

    /**
     * Finds Leads with expired TYPE_INITIAL_LOCK
     *
     * @param integer    $limit
     *
     * @return \ListBroking\AppBundle\Entity\Lead[]
     */
    public function findLeadsWithExpiredInitialLock ($limit);

    /**
     * Finds an leads by phone. The phone is unique by country
     *
     * @param string $phone
     *
     * @return Lead[]
     */
    public function findByPhone(string $phone): array;

    /**
     * Updates Lead 'in_opposition' field by phone
     *
     * @param string $phone
     * @param bool   $inOpposition
     *
     * @return int
     */
    public function updateInOppositionByPhone(string $phone, bool $inOpposition);

    /**
     * @param string $phone
     *
     * @return mixed
     */
    public function findLeadByPhone(string $phone);
}