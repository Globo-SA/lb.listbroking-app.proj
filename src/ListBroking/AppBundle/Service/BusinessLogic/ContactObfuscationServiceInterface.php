<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

interface ContactObfuscationServiceInterface
{
    /**
     * @param string $email
     * @param bool $notifyClient
     *
     * @return void
     */
    public function obfuscateContactByEmail(string $email, bool $notifyClient): void;

    /**
     * @param string $phone
     * @param bool $notifyClient
     *
     * @return void
     */
    public function obfuscateContactByPhone(string $phone, bool $notifyClient): void;
}