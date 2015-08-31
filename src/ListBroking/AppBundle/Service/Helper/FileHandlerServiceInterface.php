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

    /**
     * Generates a file using an Array
     *
     * @param      $name
     * @param      $extension
     * @param      $array
     * @param bool $zipped
     *
     * @return mixed
     */
    public function generateFileFromArray ($name, $extension, $array, $zipped = true);

}