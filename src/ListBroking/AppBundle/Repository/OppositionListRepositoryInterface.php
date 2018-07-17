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
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function importOppositionListFile($type, $config, $file);

    /**
     * @param string $phone
     *
     * @return bool
     */
    public function isPhoneInOppositionList(string $phone): bool;

    /**
     * Finds a record from a given phone
     *
     * @param string $phone
     *
     * @return mixed
     */
    public function getByPhone(string $phone);
}