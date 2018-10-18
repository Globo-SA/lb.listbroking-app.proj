<?php

namespace ListBroking\AppBundle\File;

use Exporter\Writer\JsonWriter;
use Exporter\Writer\WriterInterface;
use Exporter\Writer\XlsWriter;
use Exporter\Writer\XmlExcelWriter;
use ListBroking\AppBundle\Exporter\Exporter\CsvWriter;

/**
 * Class SonataExporterFileIO
 */
class SonataExporterFileIO extends BaseFileIO implements FileIOInterface
{
    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var string
     */
    private $filepath;

    /**
     * {@inheritdoc}
     */
    public function createFileWriter($name, $extension)
    {
        $this->filepath = $this->generateFilename($name, $extension, true, self::INTERNAL_EXPORTS_FOLDER);
        $this->writer   = $this->writerSelection($this->filepath, $extension);
    }

    /**
     * {@inheritdoc}
     */
    public function openWriter()
    {
        $this->writer->open();
    }

    /**
     * {@inheritdoc}
     */
    public function writeArray($array, $keysToIgnore = [])
    {
        foreach ($array as $row) {
            $this->writer->write(array_diff_key($row, array_flip($keysToIgnore)));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function closeWriter($zipped = true)
    {
        $this->writer->close();
        $fileInfo = $this->store(self::EXTERNAL_EXPORTS_FOLDER, $this->filepath, $zipped);

        return $fileInfo;
    }

    /**
     * Selects a file writer by type
     *
     * @param $filename
     * @param $extension
     *
     * @return WriterInterface
     */
    protected function writerSelection($filename, $extension)
    {
        switch (strtoupper($extension)) {
            case 'CSV':
                $writer = new CsvWriter($filename);
                break;
            case 'XLS':
                $writer = new XlsWriter($filename);
                break;
            case 'XML':
                $writer = new XmlExcelWriter($filename);
                break;
            case 'JSON':
                $writer = new JsonWriter($filename);
                break;
            default:
                $writer = new CsvWriter($filename);
                break;
        }

        return $writer;
    }
}
