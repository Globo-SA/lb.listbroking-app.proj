<?php

namespace ListBroking\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use ListBroking\AppBundle\Behavior\TimestampableEntityBehavior;

/**
 * ExtractionLog
 */
class ExtractionLog
{
    const CACHE_ID = 'extraction_log';

    use TimestampableEntityBehavior;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $log;

    /**
     * @var \ListBroking\AppBundle\Entity\Extraction
     */
    private $extraction;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set log
     *
     * @param string $log
     * @return ExtractionLog
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Get log
     *
     * @return string 
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Set extraction
     *
     * @param \ListBroking\AppBundle\Entity\Extraction $extraction
     * @return ExtractionLog
     */
    public function setExtraction(\ListBroking\AppBundle\Entity\Extraction $extraction = null)
    {
        $this->extraction = $extraction;

        return $this;
    }

    /**
     * Get extraction
     *
     * @return \ListBroking\AppBundle\Entity\Extraction 
     */
    public function getExtraction()
    {
        return $this->extraction;
    }
}
