<?php
/**
 * 
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2014 Adclick
 * @license    [LISTBROKING_URL_LICENSE_HERE]
 *
 * [LISTBROKING_DISCLAIMER]
 */

namespace ListBroking\AppBundle\PHPExcel;


class FileHandler {


    private $export_types;

    function __construct()
    {
        // Give PHP more resources
        ini_set('memory_limit','225M');
        set_time_limit(-1);

        //Set APC to cache cells
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3;
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

        // Export File types
        $this->export_types = array(
            'Excel5' => array('type' => 'Excel5', 'extension' => 'xls', 'label' => 'Excel File (.xls)'),
            'Excel2007' => array('type' => 'Excel2007', 'extension' => 'xlsx', 'label' => 'Excel File (.xlsx)'),
            'Excel2003XML' => array('type' => 'Excel2003XML', 'extension' => 'xml', 'label' => 'Excel File (.xml)'),
            'HTML' => array('type' => 'HTML', 'extension' => 'html', 'label' => 'HTML File (.html)'),
            'CSV' =>  array('type' => 'CSV', 'extension' => 'csv', 'label' => 'Save as a CSV file (.csv)')
        );
    }

    /**
     * Used to export a file
     * @param $filename
     * @param $headers
     * @param $array_data
     */
    public function export($filename, $headers, $array_data){

        // File Object
        $obj = new \PHPExcel();
        $sheet = $obj->getActiveSheet();

        // Writer Object
        $writer = \PHPExcel_IOFactory::createWriter($obj, $this->export_types['Excel2007']['type']);

        $header_column = 'A';
        foreach($headers as $label){
            $sheet->setCellValue("{$header_column}1", $label);
            $header_column++;
        }

        $row = 2; // 1 line is used for headers
        foreach ($array_data as $data)
        {
            $column = 'A';
            foreach ($headers as $field => $label){

                switch($field){
                    case 'lead_id':
                        $field_value = $data['lead']['id'];
                        break;
                    case 'phone':
                        $field_value = $data['lead']['phone'];
                        break;
                    case 'contact_id':
                        $field_value = $data['id'];
                        break;
                    default:
                        $field_value = $data[$field];
                        if($field_value instanceof \DateTime){
                            $field_value = $field_value->format('Y-m-d');
                        }
                        if(is_array($field_value) && array_key_exists('name', $field_value)){
                            $field_value = $field_value['name'];
                        }
                        break;
                }
                $sheet->setCellValue("{$column}{$row}", $field_value);
                $column++;
            }
            $row++;
        }

        // Save file
        $writer->save($filename);
    }


    /**
     * Used to import a file
     * @param $filename
     * @internal param $filename
     * @return \PHPExcel
     */
    public function import($filename){

        /** @var \PHPExcel_Reader_Abstract $reader */
        $reader = \PHPExcel_IOFactory::createReader(\PHPExcel_IOFactory::identify($filename));
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly(0);

        return $reader->load($filename);
    }

    /**
     * Converts a PHPExcel Object to an array
     * @param \PHPExcel $obj
     * @param $with_headers
     * @param string $endColumn
     * @return array
     */
    public function convertToArray(\PHPExcel $obj, $with_headers = true, $endColumn = 'all'){

        $headers = array();
        $array_data = array();
        $row_iterator = $obj->getActiveSheet()->getRowIterator();

        foreach ($row_iterator as $row)
        {
            foreach ($row->getCellIterator() as $cell)
            {
                if($row->getRowIndex() == 1){
                    $headers[] = $cell->getValue();
                }else {
                    if($with_headers){
                        $array_data[][$headers[$cell->getXfIndex()]] = $cell->getValue();
                    }else{
                        $array_data[] = $cell->getValue();
                    }
                }

                // If if the final column stop the foreach
                if($endColumn != 'all' && $cell->getColumn() == $endColumn){
                    break;
                }
            }
        }

        return $array_data;
    }
} 