<?php

namespace ListBroking\AppBundle\Repository;

interface OppositionListRepositoryInterface
{
    /**
     * Imports an Opposition list file by type, clears old values by default
     *
     * @param $type
     * @param $config
     * @param $file
     * @param bool $clear_old
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function importOppositionListFile($type, $config, $file, $clear_old = true);

    /**
     * @param string $phone
     *
     * @return bool
     */
    public function isPhoneInOppositionList(string $phone): bool;

    /**
     * @param string $phone
     *
     * @return mixed
     */
    public function findByPhone(string $phone);
}