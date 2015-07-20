<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */
namespace ListBroking\AppBundle\Service\Helper;

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
}