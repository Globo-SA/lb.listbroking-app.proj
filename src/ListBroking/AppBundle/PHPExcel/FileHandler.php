<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\PHPExcel;

use Doctrine\ORM\Query;
use Exporter\Handler;
use Exporter\Source\DoctrineORMQuerySourceIterator;
use Exporter\Writer\CsvWriter;
use Exporter\Writer\JsonWriter;
use Exporter\Writer\XlsWriter;
use Exporter\Writer\XmlExcelWriter;

class FileHandler
{

    // Export File types
    public static $export_types = array(
        'XLS'    => array('type' => 'Excel2007', 'extension' => 'xlsx', 'label' => 'Excel File (.xlsx)'),
        'CSV'          => array('type' => 'CSV', 'extension' => 'csv', 'label' => 'Save as a CSV file (.csv)'),
        'XML'          => array('type' => 'XML', 'extension' => 'xml', 'label' => 'Save as a XML file (.xml)'),
        'JSON'          => array('type' => 'JSON', 'extension' => 'json', 'label' => 'Save as a JSON file (.json)')
    );

    function __construct ()
    {
        //Set APC to cache cells
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3;
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
    }

    /**
     * Used to export a file
     *
     * @param string $filename
     * @param array  $headers
     * @param string $type
     * @param Query  $query
     */
    public function export ($filename, $headers, $type, Query $query)
    {

        $source = new DoctrineORMQuerySourceIterator($query, $headers);
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
     * Used to import a file
     *
     * @param $filename
     *
     * @internal param $filename
     * @return \PHPExcel
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
     * Converts a PHPExcel Object to an array
     *
     * @param \PHPExcel $obj
     * @param           $with_headers
     * @param string    $endColumn
     *
     * @return array
     */
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
} 