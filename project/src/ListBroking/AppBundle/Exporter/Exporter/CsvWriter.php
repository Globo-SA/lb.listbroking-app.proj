<?php

namespace ListBroking\AppBundle\Exporter\Exporter;

use Exporter\Writer\CsvWriter as BaseCsvWriter;

/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2016 Adclick
 */
class CsvWriter extends BaseCsvWriter
{

    /**
     * @param string $filename
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @param bool   $showHeaders
     * @param bool   $withBom
     */
    public function __construct($filename, $delimiter = ',', $enclosure = '"', $escape = '\\', $showHeaders = true, $withBom = false)
    {
        $this->filename    = $filename;
        $this->delimiter   = $delimiter;
        $this->enclosure   = $enclosure;
        $this->escape      = $escape;
        $this->showHeaders = $showHeaders;
        $this->position    = 0;
        $this->withBom     = $withBom;
    }

    /**
     * @inheritDoc
     */
    public function open()
    {
        $this->file = fopen($this->filename, 'a', false);
        if (true === $this->withBom) {
            fprintf($this->file, chr(0xEF).chr(0xBB).chr(0xBF));
        }
    }
}