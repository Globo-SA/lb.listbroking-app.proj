<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */
namespace ListBroking\AppBundle\Service\Helper;

use Doctrine\ORM\Query;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\File;

interface FileHandlerServiceInterface
{
    /**
     * Used to import a file
     *
     * @param $filename
     *
     * @internal param $filename
     * @return \PHPExcel
     */
    public function import ($filename);

    /**
     * Converts a PHPExcel Object to an array
     *
     * @param \PHPExcel $obj
     * @param           $with_headers
     * @param string    $endColumn
     *
     * @return array
     */
    public function convertToArray (\PHPExcel $obj, $with_headers = true, $endColumn = 'all');

    /**
     * Saves a file on a form
     *
     * @param Form $form
     *
     * @return File
     */
    public function saveFormFile (Form $form);

    /**
     * Generates a file using a Query object
     * @param      $name
     * @param      $extension
     * @param      $query
     * @param      $headers
     * @param bool $zipped
     *
     * @return mixed
     */
    public function generateFileFromQuery($name, $extension, Query $query, $headers, $zipped = true);

}