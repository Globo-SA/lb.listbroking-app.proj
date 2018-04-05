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
     * @return \PHPExcel
     */
    public function loadExcelFile($filename);

    /**
     * Saves a file on a form
     *
     * @param Form $form
     *
     * @return File
     */
    public function saveFormFile (Form $form);

    /**
     * Generates a file using an Array
     *
     * @param string $name
     * @param string $extension
     * @param array  $array
     * @param bool   $zipped
     * @param bool   $upload
     *
     * @return array
     */
    public function generateFileFromArray ($name, $extension, $array, $zipped = true, $upload = true);

    /**
     * Generates a file Writer
     *
     * @param        $name
     * @param string $extension
     */
    public function createFileWriter($name, $extension);

    /**
     * Iterates an array with a given Writer
     *
     * @param array $array
     * @param array $keys_to_ignore
     *
     * @return
     */
    public function writeArray($array, $keys_to_ignore = array());

    /**
     * Opens a given writer
     *
     * @return mixed
     */
    public function openWriter();

    /**
     * Closes a given Writer
     *
     * @param bool $zipped
     *
     * @return string
     */
    public function closeWriter($zipped = true);
}
