<?php

namespace ListBroking\AppBundle\Service\BusinessLogic;

interface ContactObfuscationServiceInterface
{
    /**
     * Verify if phone is already obfuscated in an opposition list
     *
     * @param string $phone
     *
     * @return bool
     */
    public function isPhoneObfuscatedInOppositionList(string $phone): bool;

    /**
     * This will obfuscate:
     * - All lead's information
     * - All lead's contacts information
     * - All contacts extraction deduplications
     * - All contacts opposition lists
     * - All emails/phone's opposition lists
     *
     * This will also delete:
     * - All lead's staging contact information
     * - All emails/phones's staging contact information
     *
     * @param array  $leads
     * @param string $email
     * @param string $phone
     *
     * @return bool
     */
    public function obfuscateAllContactData(array $leads, string $email, string $phone): bool;

    /**
     * This will obfuscate:
     * - All lead's hist information
     * - All contacts's hist information
     *
     * @param array  $leadsHist
     * @param string $email
     * @param string $phone
     *
     * @return bool
     */
    public function obfuscateAllContactHistData(array $leadsHist, string $email, string $phone): bool;
}