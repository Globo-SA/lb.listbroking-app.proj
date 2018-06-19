<?php

namespace ListBroking\AppBundle\Repository;

interface StagingContactProcessedRepositoryInterface
{
    /**
     * Cleanup records equal or older than id.
     *
     * @param $id
     *
     * @return mixed
     */
    public function cleanUp($id);

    /**
     * @param string $email
     * @param string $phone
     *
     * @return mixed
     */
    public function deleteContactByEmailOrPhone(string $email, string $phone);
}