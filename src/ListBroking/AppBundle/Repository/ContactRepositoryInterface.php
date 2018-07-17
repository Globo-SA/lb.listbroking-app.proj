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
}