<?php

namespace ListBroking\AppBundle\Entity;

use ListBroking\AppBundle\Behavior\BlameableEntityBehavior;
use ListBroking\AppBundle\Behavior\TimestampableEntityBehavior;
use Doctrine\ORM\Mapping as ORM;

/**
 * ExtractionDeduplicationQueue
 */
class ExtractionDeduplicationQueue
{
    const CACHE_ID = 'extraction_deduplication_queue';

    use TimestampableEntityBehavior,
        BlameableEntityBehavior;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $filePath;

    private $extraction;

    private $field;


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
     * @return mixed
     */
    public function getExtraction()
    {
        return $this->extraction;
    }

    /**
     * @param Extraction $extraction
     */
    public function setExtraction(Extraction $extraction)
    {
        $this->extraction = $extraction;
    }

    /**
     * Set filePath
     *
     * @param string $filePath
     * @return ExtractionDeduplicationQueue
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Get filePath
     *
     * @return string 
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }


}
