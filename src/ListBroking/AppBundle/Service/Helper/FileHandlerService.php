<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\Service\Helper;

use Doctrine\ORM\Query;
use Exporter\Handler;
use Exporter\Source\ArraySourceIterator;
use Exporter\Source\DoctrineORMQuerySourceIterator;
use Exporter\Writer\CsvWriter;
use Exporter\Writer\JsonWriter;
use Exporter\Writer\XlsWriter;
use Exporter\Writer\XmlExcelWriter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

class FileHandlerService implements FileHandlerServiceInterface
{

    public static $export_types = array(
        'XLS'  => array('type' => 'Excel2007', 'extension' => 'xlsx', 'label' => 'Excel File (.xlsx)'),
        'CSV'  => array('type' => 'CSV', 'extension' => 'csv', 'label' => 'Save as a CSV file (.csv)'),
        'XML'  => array('type' => 'XML', 'extension' => 'xml', 'label' => 'Save as a XML file (.xml)'),
        'JSON' => array('type' => 'JSON', 'extension' => 'json', 'label' => 'Save as a JSON file (.json)')
    );

    // Export File types

    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct (KernelInterface $kernel)
    {
        $this->kernel = $kernel;

        //Set APC to cache cells
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3;
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
    }

    /**
     * @inheritdoc
     */
    public function generateFileFromQuery ($name, $extension, Query $query, $headers, $zipped = true)
    {
        // Generate File
        $filename = $this->generateFilename($name, $extension, true, '/../web/exports/');
        $this->exportByQuery($filename, $extension, $headers, $query);

        if ( $zipped )
        {
            return $this->zipFile($filename, true);
        }

        return array($filename, null);
    }

    /**
     * @inheritdoc
     */
    public function generateFileFromArray ($name, $extension, $array, $zipped = true)
    {
        // Generate File
        $filename = $this->generateFilename($name, $extension, true, '/../web/exports/');
        $this->exportByArray($filename, $extension, $array);

        if ( $zipped )
        {
            return $this->zipFile($filename, true);
        }

        return array($filename, null);
    }

    /**
     * @inheritdoc
     */
    public function import ($filename)
    {
        /** @var \PHPExcel_Reader_Abstract $reader */
        $reader = \PHPExcel_IOFactory::createReader(\PHPExcel_IOFactory::identify($filename));
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly(0);

        return $reader->load($filename);
    }

    /**
     * @inheritdoc
     */
    public function saveFormFile (Form $form)
    {
        // Handle Form
        $data = $form->getData();
        /** @var UploadedFile $uploaded_file */
        $uploaded_file = $data['upload_file'];
        $filename = $this->generateFilename($uploaded_file->getClientOriginalName(), null, 'imports/');

        return $uploaded_file->move('imports', $filename);
    }

    /**
     * Generates the filename and generate a filename for it
     *
     * @param        $name
     * @param        $extension
     * @param bool   $absolute
     * @param string $dir
     *
     * @return string
     */
    private function generateFilename ($name, $extension, $absolute = false, $dir = '/')
    {
        // Make sure the file is unique
        $name = uniqid('listbroking', true) . str_replace(' ', '-', $name);

        $filename = strtolower(preg_replace('/\s/i', '-', $dir . date('Y-m-d'))) . $name;
        if ( $extension )
        {
            $filename = $filename . '.' . $extension;
        }

        if ( $absolute )
        {
            $filename = $this->kernel->getRootDir() . $filename;
        }

        return $filename;
    }

    /**
     * Used to export a file using a Query
     *
     * @param string $filename
     * @param        $extension
     * @param array  $headers
     * @param Query  $query
     *
     * @internal param string $type
     */
    private function exportByQuery ($filename, $extension, $headers, Query $query)
    {

        $source = new DoctrineORMQuerySourceIterator($query, $headers, 'Y-m-d');
        $writer = $this->writerSelection($extension, $filename);

        Handler::create($source, $writer)
               ->export()
        ;
    }

    /**
     * Used to export a file using an Array
     *
     * @param $filename
     * @param $extension
     * @param $array
     */
    private function exportByArray ($filename, $extension, $array)
    {
        $source = new ArraySourceIterator($array);
        $writer = $this->writerSelection($extension, $filename);

        Handler::create($source, $writer)
               ->export()
        ;
    }

    /**
     * Selects a file writer by type
     *
     * @param $extension
     * @param $filename
     *
     * @return CsvWriter|JsonWriter|XlsWriter|XmlExcelWriter
     */
    private function writerSelection ($extension, $filename)
    {
        switch ( $extension )
        {
            case 'CSV':
                $writer = new CsvWriter($filename);
                break;
            case 'Excel':
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
     * Simple password generator
     *
     * @param int $length
     *
     * @return string
     */
    private function generatePassword ($length = 10)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ( $i = 0, $result = ''; $i < $length; $i++ )
        {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }

    /**
     * Zips a given file with optional password protection
     *
     * @param      $filename
     * @param      $zip_name
     * @param bool $with_password
     *
     * @return array
     */
    private function zipFile ($filename, $zip_name, $with_password = true)
    {
        $zipped_filename = $this->generateFilename($zip_name, 'zip', true, '/../web/exports/');

        if ( $with_password )
        {
            $password = $this->generatePassword();
            exec(sprintf("zip -j --password %s %s %s", $password, $zipped_filename, $filename));

            // Remove the original File
            unlink($filename);

            return array($zip_name, $password);
        }
        exec(sprintf("zip -j %s %s", $zipped_filename, $filename));

        // Remove the original File
        unlink($filename);

        return array($zip_name, null);
    }
} 