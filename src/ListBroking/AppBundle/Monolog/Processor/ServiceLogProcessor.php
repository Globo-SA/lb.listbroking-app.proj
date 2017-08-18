<?php

namespace ListBroking\AppBundle\Monolog\Processor;

/**
 * ListBroking\AppBundle\Monolog\Processor\ServiceLogProcessor
 */
class ServiceLogProcessor
{
    /**
     * @var ServiceLogTaskIdentifierInterface
     */
    protected $serviceLogTaskIdentifier;

    /**
     * @param ServiceLogTaskIdentifierInterface $serviceLogTaskIdentifier
     */
    public function setServiceLogTaskIdentifier(ServiceLogTaskIdentifierInterface $serviceLogTaskIdentifier)
    {
        $this->serviceLogTaskIdentifier = $serviceLogTaskIdentifier;
    }

    /**
     * @param array $record
     *
     * @return array
     */
    public function processRecord(array $record)
    {
        if ($this->serviceLogTaskIdentifier->getLogIdentifier()) {
            $identifier = sprintf(
                '%s-%s',
                getmypid(),
                $this->serviceLogTaskIdentifier->getLogIdentifier()
            );
            $record['extra'] = [];
            $record['extra']['task_identifier'] = $identifier;
        }

        return $record;
    }
}
