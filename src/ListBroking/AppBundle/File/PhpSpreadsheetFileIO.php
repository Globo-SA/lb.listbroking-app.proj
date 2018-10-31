<?php

namespace ListBroking\AppBundle\File;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

/**
 * ListBroking\AppBundle\File\PhpSpreadsheetFileIO
 */
class PhpSpreadsheetFileIO extends BaseFileIO implements FileIOInterface
{
    /**
     * @var Spreadsheet
     */
    private $spreadsheet;

    /**
     * @var Worksheet
     */
    private $sheet;

    /**
     * @var string
     */
    private $filepath;

    /**
     * {@inheritdoc}
     */
    public function createFileWriter($name, $extension)
    {
        // Generate File
        $this->filepath = $this->generateFilename($name, 'xls', true, self::INTERNAL_EXPORTS_FOLDER);

        $this->spreadsheet = new Spreadsheet();
        $this->sheet       = $this->spreadsheet->getActiveSheet();
    }

    /**
     * {@inheritdoc}
     */
    public function openWriter()
    {
        // nothing to do
    }

    /**
     * {@inheritdoc}
     */
    public function writeArray($array, $keysToIgnore = [])
    {
        // start at index 2, because header row is index 1
        $currentRow = 2;

        foreach ($array as $row) {
            $currentColumn = 1;

            foreach ($row as $key => $value) {

                if (in_array($key, $keysToIgnore)) {

                    continue;
                }

                // define header row
                if ($currentRow === 2) {
                    $cell = $this->sheet->getCellByColumnAndRow($currentColumn, 1);
                    $cell->setValue($key);
                }

                $cell = $this->sheet->getCellByColumnAndRow($currentColumn, $currentRow);
                $cell->setValue($value);

                // guarantee that leading zeros on numeric values are maintained
                if (is_numeric($value) && strpos($value, '0') === 0) {
                    $cell->setValueExplicit($value, DataType::TYPE_STRING);
                }

                $currentColumn++;
            }

            $currentRow++;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function closeWriter($zipped = true)
    {
        $writer = new Xls($this->spreadsheet);
        $writer->save($this->filepath);

        $fileInfo = $this->store(self::EXTERNAL_EXPORTS_FOLDER, $this->filepath, $zipped);

        return $fileInfo;
    }
}
