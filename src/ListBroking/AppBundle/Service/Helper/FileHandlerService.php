<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\Helper;

use Adclick\Components\GDPR\Service\ZipFileServiceInterface;
use Doctrine\ORM\Query;
use Exporter\Handler;
use Exporter\Source\ArraySourceIterator;
use Exporter\Writer\JsonWriter;
use Exporter\Writer\WriterInterface;
use Exporter\Writer\XlsWriter;
use Exporter\Writer\XmlExcelWriter;
use ListBroking\AppBundle\Exporter\Exporter\CsvWriter;
use ListBroking\AppBundle\Exporter\Source\DoctrineORMQuerySourceIterator;
use ListBroking\AppBundle\File\BaseFile;
use ListBroking\AppBundle\File\FileIOInterface;
use ListBroking\AppBundle\File\PhpSpreadsheetFileIO;
use ListBroking\AppBundle\File\SonataExporterFileIO;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * ListBroking\AppBundle\Service\Helper\FileHandlerService
 */
class FileHandlerService extends BaseFile implements FileHandlerServiceInterface
{
    /**
     * @var ZipFileServiceInterface
     */
    protected $zipFileService;

    /**
     * @var FileIOInterface
     */
    protected $fileIO;

    /**
     * FileHandlerService constructor.
     *
     * @param string                  $projectRootDir
     * @param ZipFileServiceInterface $zipFileService
     */
    public function __construct(string $projectRootDir, ZipFileServiceInterface $zipFileService)
    {
        parent::__construct($projectRootDir);

        $this->zipFileService = $zipFileService;

        // Set APC to cache cells
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3;
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFileFromArray($name, $extension, $array, $zipped = true, $upload = true)
    {
        // Generate File
        $filePath = $this->generateFilename($name, $extension, true, self::INTERNAL_EXPORTS_FOLDER);

        // Export and store
        $this->exportByArray($filePath, $extension, $array);

        $fileInfo = [dirname($filePath), basename($filePath), ''];

        return $fileInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function loadExcelFile($filename)
    {
        /** @var \PHPExcel_Reader_Abstract $reader */
        $reader   = \PHPExcel_IOFactory::createReader(\PHPExcel_IOFactory::identify($filename));
        $fileinfo = pathinfo($filename);
        if (strtoupper($fileinfo['extension']) == 'CSV') {
            $reader    = new \PHPExcel_Reader_CSV();
            $delimiter = $this->findCsvDelimiter($filename);
            if ($delimiter) {
                $reader->setDelimiter($delimiter);
            }
        }

        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly(0);

        return $reader->load($filename);
    }

    /**
     * {@inheritdoc}
     */
    public function saveFormFile(Form $form)
    {
        // Handle Form
        $data = $form->getData();

        /** @var UploadedFile $uploaded_file */
        $uploaded_file = $data['upload_file'];
        $path          = $this->generateFilename(
            $uploaded_file->getClientOriginalName(),
            null,
            true,
            self::INTERNAL_IMPORTS_FOLDER
        );

        return $uploaded_file->move(self::EXTERNAL_IMPORTS_FOLDER, $path);
    }

    /**
     * Generates a file Writer
     *
     * @param string $name
     * @param string $extension
     */
    public function createFileWriter($name, $extension)
    {
        if ($extension === 'xls') {
            $this->fileIO = new PhpSpreadsheetFileIO($this->projectRootDir, $this->zipFileService);
        } else {
            $this->fileIO = new SonataExporterFileIO($this->projectRootDir, $this->zipFileService);
        }

        $this->fileIO->createFileWriter($name, $extension);
    }

    /**
     * {@inheritdoc}
     */
    public function openWriter()
    {
        $this->fileIO->openWriter();
    }

    /**
     * {@inheritdoc}
     */
    public function writeArray($array, $keysToIgnore = [])
    {
        $this->fileIO->writeArray($array, $keysToIgnore);
    }

    /**
     * {@inheritdoc}
     */
    public function closeWriter($zipped = true)
    {
        return $this->fileIO->closeWriter($zipped);
    }

    /**
     * Used to export a file using an Array
     *
     * @param $filename
     * @param $extension
     * @param $array
     */
    private function exportByArray($filename, $extension, $array)
    {
        $source = new ArraySourceIterator($array);
        $writer = $this->writerSelection($filename, $extension);

        Handler::create($source, $writer)
               ->export();
    }

    /**
     * Selects a file writer by type
     *
     * @param $filename
     * @param $extension
     *
     * @return WriterInterface
     */
    private function writerSelection($filename, $extension)
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

    /**
     * Finds the delimiter of a CSV file
     *
     * @param     $filepath
     * @param int $checkLines
     *
     * @return mixed
     */
    private function findCsvDelimiter($filepath, $checkLines = 2)
    {
        $file       = new \SplFileObject($filepath);
        $delimiters = [
            ',',
            '\t',
            ';',
            '|',
            ':',
        ];
        $results    = [];
        $i          = 0;
        while ($file->valid() && $i <= $checkLines) {
            $line = $file->fgets();
            foreach ($delimiters as $delimiter) {
                $regExp = '/[' . $delimiter . ']/';
                $fields = preg_split($regExp, $line);
                if (count($fields) > 1) {
                    if (!empty($results[$delimiter])) {
                        $results[$delimiter]++;
                    } else {
                        $results[$delimiter] = 1;
                    }
                }
            }
            $i++;
        }

        if (empty($results)) {
            return null;
        }

        $results = array_keys($results, max($results));

        return $results[0];
    }
}
