<?php
/**
 * @author     Samuel Castro <samuel.castro@adclick.pt>
 * @copyright  2015 Adclick
 */
namespace ListBroking\AppBundle\Service\Helper;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Interface FileHandlerServiceInterface
 */
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
    public function saveFormFile(Form $form);

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
    public function generateFileFromArray($name, $extension, $array, $zipped = true, $upload = true);

    /**
     * Generates a file Writer
     *
     * @param string $name
     * @param string $extension
     */
    public function createFileWriter($name, $extension);

    /**
     * Opens a given writer
     *
     * @return mixed
     */
    public function openWriter();

    /**
     * Iterates an array with a given Writer
     *
     * @param array $array
     * @param array $keysToIgnore
     */
    public function writeArray($array, $keysToIgnore = []);

    /**
     * Closes a given Writer
     *
     * @param bool $zipped
     *
     * @return string
     */
    public function closeWriter($zipped = true);
}
