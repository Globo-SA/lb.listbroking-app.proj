<?php

namespace ListBroking\AppBundle\Repository;

interface ContactRepositoryInterface
{
    /**
     * @param string $email
     *
     * @return mixed
     */
    public function findContactByEmail(string $email);
}