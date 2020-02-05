<?php

namespace ListBroking\AppBundle\File;

/**
 * ListBroking\AppBundle\File\FileIOInterface
 */
interface FileIOInterface
{
    /**
     * Generates a file Writer
     *
     * @param string $name
     * @param string $extension
     */
    public function createFileWriter($name, $extension);

    /**
     * Opens a given writer
     */
    public function openWriter();

    /**
     * Writes an array with a given Writer
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
