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

    function __construct (KernelInterface $kernel)
    {
        $this->kernel = $kernel;

        //Set APC to cache cells
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3;
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
    }

    public function convertToArray (\PHPExcel $obj, $with_headers = true, $endColumn = 'all')
    {

        $headers = array();
        $array_data = array();
        $row_iterator = $obj->getActiveSheet()
                            ->getRowIterator()
        ;

        foreach ( $row_iterator as $row )
        {
            foreach ( $row->getCellIterator() as $cell )
            {
                if ( $row->getRowIndex() == 1 )
                {
                    $headers[] = $cell->getValue();
                }
                else
                {
                    if ( $with_headers )
                    {
                        $array_data[][$headers[$cell->getXfIndex()]] = $cell->getValue();
                    }
                    else
                    {
                        $array_data[] = $cell->getValue();
                    }
                }

                // If if the final column stop the foreach
                if ( $endColumn != 'all' && $cell->getColumn() == $endColumn )
                {
                    break;
                }
            }
        }

        return $array_data;
    }

    public function generateFileFromQuery ($name, $extension, Query $query, $headers, $zipped = true)
    {
        // Make sure the file is unique
        $name = uniqid() . $name;

        // Generate File
        $filename = $this->generateFilename($name, $extension, true, '/../web/exports/');
        $this->exportByQuery($filename, $headers, $extension, $query);

        if ( $zipped )
        {
            // Generate Zip File and add Password
            $password = $this->generatePassword();
            $zipped_filename = $this->generateFilename($name, 'zip', true, '/../web/exports/');
            exec(sprintf("zip -j --password %s %s %s", $password, $zipped_filename, $filename));

            // Remove the original File
            unlink($filename);

            return array($zipped_filename, $password);
        }

        return array($filename, null);
    }

    public function import ($filename)
    {
        /** @var \PHPExcel_Reader_Abstract $reader */
        $reader = \PHPExcel_IOFactory::createReader(\PHPExcel_IOFactory::identify($filename));
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly(0);

        return $reader->load($filename);
    }

    /**
     * @param Form $form
     *
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function saveFormFile (Form $form)
    {
        // Handle Form
        $data = $form->getData();
        /** @var UploadedFile $file */
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
        $name = str_replace(' ', '-', $name);

        $filename = strtolower(preg_replace('/\s/i', '-', $dir . $name . date('Y-m-d')));
        if($extension){
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
     * @param array  $headers
     * @param string $type
     * @param Query  $query
     */
    private function exportByQuery ($filename, $headers, $type, Query $query)
    {

        $source = new DoctrineORMQuerySourceIterator($query, $headers, 'Y-m-d');
        switch ( $type )
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

        Handler::create($source, $writer)
               ->export()
        ;
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
} 