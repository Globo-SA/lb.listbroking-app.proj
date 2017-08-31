<?php

namespace ListBroking\AppBundle\Monolog\Processor;

/**
 * ListBroking\AppBundle\Monolog\Processor\ServiceLogTaskIdentifierInterface
 */
interface ServiceLogTaskIdentifierInterface
{
    /**
     * Get log identifier
     *
     * @return string
     */
    public function getLogIdentifier();

    /**
     * Set log identifier
     *
     * @param string $logIdentifier
     *
     * @return ServiceLogTaskIdentifier
     */
    public function setLogIdentifier($logIdentifier);
}
