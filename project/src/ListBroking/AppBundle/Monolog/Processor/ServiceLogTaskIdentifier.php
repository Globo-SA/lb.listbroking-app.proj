<?php

namespace ListBroking\AppBundle\Monolog\Processor;

/**
 * ListBroking\AppBundle\Monolog\Processor\ServiceLogTaskIdentifier
 */
class ServiceLogTaskIdentifier implements ServiceLogTaskIdentifierInterface
{
    /**
     * @var string
     */
    protected $logIdentifier;

    /**
     * {@inheritdoc}
     */
    public function getLogIdentifier()
    {
        return $this->logIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogIdentifier($logIdentifier)
    {
        $this->logIdentifier = $logIdentifier;

        return $this;
    }
}
