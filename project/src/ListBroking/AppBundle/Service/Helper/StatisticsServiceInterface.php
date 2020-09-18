<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */
namespace ListBroking\AppBundle\Service\Helper;

use ListBroking\AppBundle\Model\AudiencesFilter;

interface StatisticsServiceInterface
{
    /**
     * Generates the current Contact statistics
     *
     * @param $data
     *
     * @return bool
     */
    public function generateStatisticsQuery ($data);

    /**
     * Calculates audiences based on:
     * - Owner
     * - Country
     * - District
     * - Category
     * - Gender
     * - Age
     */
    public function calculateAudiences(): void;

    /**
     * Get audiences based on the given filter
     *
     * @param AudiencesFilter $filter
     *
     * @return array
     */
    public function getAudiences(AudiencesFilter $filter): array;
}
