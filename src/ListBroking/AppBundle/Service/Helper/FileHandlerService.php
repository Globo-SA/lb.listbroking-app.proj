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
use Exporter\Writer\JsonWriter;
use Exporter\Writer\WriterInterface;
use Exporter\Writer\XlsWriter;
use Exporter\Writer\XmlExcelWriter;
use League\Flysystem\Filesystem;
use ListBroking\AppBundle\Exporter\Exporter\CsvWriter;
use ListBroking\AppBundle\Exporter\Source\DoctrineORMQuerySourceIterator;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

class FileHandlerService implements FileHandlerServiceInterface
{

    const EXTERNAL_EXPORTS_FOLDER = 'exports/';

    const EXTERNAL_IMPORTS_FOLDER = 'imports/';

    const INTERNAL_EXPORTS_FOLDER = '/../web/exports/';

    const INTERNAL_IMPORTS_FOLDER = '/../web/imports/';

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    private $filesystem_config;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var string
     */
    private $filepath;

    public function __construct (KernelInterface $kernel, Filesystem $filesystem, $filesystem_config)
    {
        $this->kernel = $kernel;
        $this->filesystem = $filesystem;
        $this->filesystem_config = $filesystem_config;

        //Set APC to cache cells
        $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3;
        \PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
    }

    /**
     * @inheritdoc
     */
    public function generateFileFromQuery ($name, $extension, Query $query, $headers, $zipped = true, $upload = true)
    {
        // Generate File
        $filepath = $this->generateFilename($name, $extension, true, self::INTERNAL_EXPORTS_FOLDER);

        // Export and store
        $this->exportByQuery($filepath, $extension, $headers, $query);

        $file_info = array($filepath, '');
        if($upload){
            $file_info = $this->store(self::EXTERNAL_EXPORTS_FOLDER, $filepath, $zipped);
        }

        return $file_info;
    }

    /**
     * @inheritdoc
     */
    public function generateFileFromArray ($name, $extension, $array, $zipped = true, $upload = true)
    {
        // Generate File
        $filepath = $this->generateFilename($name, $extension, true, self::INTERNAL_EXPORTS_FOLDER);

        // Export and store
        $this->exportByArray($filepath, $extension, $array);

        $file_info = array($filepath, '');
        if($upload){
            $file_info = $this->store(self::EXTERNAL_EXPORTS_FOLDER, $filepath, $zipped);
        }

        return $file_info;
    }

    /**
     * @inheritdoc
     */
    public function createFileWriter($name, $extension)
    {
        // Generate File
        $this->filepath = $this->generateFilename($name, $extension, true, self::INTERNAL_EXPORTS_FOLDER);

        $this->writer = $this->writerSelection($this->filepath, $extension);
    }
    
    /**
     * @inheritDoc
     */
    public function writeArray($array)
    {
        foreach ($array as $row)
        {
            $this->writer->write($row);
        }
    }

    /**
     * @inheritDoc
     */
    public function openWriter()
    {
        $this->writer->open();
    }

    /**
     * @inheritDoc
     */
    public function closeWriter($zipped = true)
    {
        $this->writer->close();
        $file_info = $this->store(self::EXTERNAL_EXPORTS_FOLDER, $this->filepath, $zipped);

        return $file_info;
    }

    /**
     * @inheritdoc
     */
    public function loadExcelFile($filename)
    {
        /** @var \PHPExcel_Reader_Abstract $reader */
        $reader = \PHPExcel_IOFactory::createReader(\PHPExcel_IOFactory::identify($filename));
        $fileinfo = pathinfo($filename);
        if(strtoupper($fileinfo['extension']) == 'CSV'){
            $delimiter = $this->findCsvDelimiter($filename);
            $reader->setDelimiter($delimiter);
        }

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
        $path = $this->generateFilename($uploaded_file->getClientOriginalName(), null, true, self::INTERNAL_IMPORTS_FOLDER);

        return $uploaded_file->move(self::EXTERNAL_IMPORTS_FOLDER, $path);
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
        $path = $dir . $this->cleanUpName($name, strtolower($extension));
        if ( $absolute )
        {
            $path = $this->kernel->getRootDir() . $path;
        }

        return $path;
    }

    /**
     * Used to export a file using a Query
     *
     * @param string $filename
     * @param        $extension
     * @param array  $headers
     * @param Query  $query
     */
    private function exportByQuery ($filename, $extension, $headers, Query $query)
    {
        $source = new DoctrineORMQuerySourceIterator($query, $headers, 'Y-m-d');
        $writer = $this->writerSelection($filename, $extension);

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
        $writer = $this->writerSelection($filename, $extension);

        Handler::create($source, $writer)
               ->export()
        ;
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
        switch ( strtoupper($extension) )
        {
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
     * @param      $path
     * @param bool $with_password
     *
     * @return array
     */
    private function zipFile ($path, $with_password = true)
    {
        $path_info = pathinfo($path);
        $zipped_path = $path_info['dirname'] . '/' . $path_info['filename'] . '.zip';

        if ( $with_password )
        {
            $password = $this->generatePassword();
            exec(sprintf("zip -j --password %s %s %s", $password, $zipped_path, $path));

            // Remove the original File
            unlink($path);

            return array($zipped_path, $password);
        }
        exec(sprintf("zip -j %s %s", $zipped_path, $path));

        // Remove the original File
        unlink($path);

        return array($zipped_path, null);
    }

    /**
     * @param string  $path
     * @param string  $filename
     * @param    bool $zipped
     *
     * @return string
     */
    private function store ($path, $filename, $zipped)
    {
        $file_info = array($filename, '');
        if ( $zipped )
        {
            $file_info = $this->zipFile($filename, true);
        }

        $s3_path = $path . pathinfo($file_info[0], PATHINFO_BASENAME);
        $stream = fopen($file_info[0], 'r+');
        $this->filesystem->writeStream($s3_path, $stream, array('ACL' => 'public-read'));
        fclose($stream);

        unlink($file_info[0]);
        $file_info[0] = $this->generateFilesystemURL($s3_path);

        return $file_info;
    }

    private function cleanUpName ($name, $extension = null)
    {
        $fileinfo = pathinfo($name);

        if ( ! $extension )
        {
            $extension = $fileinfo['extension'];
        }

        $filename = preg_replace('/\s/i', '-', $fileinfo['filename']);
        $filename = preg_replace('/\(duplicate\)/i', '', $filename);
        $filename = preg_replace("/[^[:alnum:]]/ui", '', $filename);
        $filename = $this->removeAccents($filename);

        // uniqid() used to make sure the file is unique
        return strtolower(sprintf('%s-%s-%s.%s', $filename, uniqid(), date('Y-m-d'), $extension));
    }

    private function generateFilesystemURL ($path)
    {
        return(sprintf('%s/%s', $this->filesystem_config['url'], $path));
    }

    /**
     * Official Worldpress function to remove accents from strings
     * @param $string
     *
     * @return string
     */
    private function removeAccents ($string)
    {
        if ( ! preg_match('/[\x80-\xff]/', $string) )
        {
            return $string;
        }

        $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195) . chr(128) => 'A',
            chr(195) . chr(129) => 'A',
            chr(195) . chr(130) => 'A',
            chr(195) . chr(131) => 'A',
            chr(195) . chr(132) => 'A',
            chr(195) . chr(133) => 'A',
            chr(195) . chr(135) => 'C',
            chr(195) . chr(136) => 'E',
            chr(195) . chr(137) => 'E',
            chr(195) . chr(138) => 'E',
            chr(195) . chr(139) => 'E',
            chr(195) . chr(140) => 'I',
            chr(195) . chr(141) => 'I',
            chr(195) . chr(142) => 'I',
            chr(195) . chr(143) => 'I',
            chr(195) . chr(145) => 'N',
            chr(195) . chr(146) => 'O',
            chr(195) . chr(147) => 'O',
            chr(195) . chr(148) => 'O',
            chr(195) . chr(149) => 'O',
            chr(195) . chr(150) => 'O',
            chr(195) . chr(153) => 'U',
            chr(195) . chr(154) => 'U',
            chr(195) . chr(155) => 'U',
            chr(195) . chr(156) => 'U',
            chr(195) . chr(157) => 'Y',
            chr(195) . chr(159) => 's',
            chr(195) . chr(160) => 'a',
            chr(195) . chr(161) => 'a',
            chr(195) . chr(162) => 'a',
            chr(195) . chr(163) => 'a',
            chr(195) . chr(164) => 'a',
            chr(195) . chr(165) => 'a',
            chr(195) . chr(167) => 'c',
            chr(195) . chr(168) => 'e',
            chr(195) . chr(169) => 'e',
            chr(195) . chr(170) => 'e',
            chr(195) . chr(171) => 'e',
            chr(195) . chr(172) => 'i',
            chr(195) . chr(173) => 'i',
            chr(195) . chr(174) => 'i',
            chr(195) . chr(175) => 'i',
            chr(195) . chr(177) => 'n',
            chr(195) . chr(178) => 'o',
            chr(195) . chr(179) => 'o',
            chr(195) . chr(180) => 'o',
            chr(195) . chr(181) => 'o',
            chr(195) . chr(182) => 'o',
            chr(195) . chr(182) => 'o',
            chr(195) . chr(185) => 'u',
            chr(195) . chr(186) => 'u',
            chr(195) . chr(187) => 'u',
            chr(195) . chr(188) => 'u',
            chr(195) . chr(189) => 'y',
            chr(195) . chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196) . chr(128) => 'A',
            chr(196) . chr(129) => 'a',
            chr(196) . chr(130) => 'A',
            chr(196) . chr(131) => 'a',
            chr(196) . chr(132) => 'A',
            chr(196) . chr(133) => 'a',
            chr(196) . chr(134) => 'C',
            chr(196) . chr(135) => 'c',
            chr(196) . chr(136) => 'C',
            chr(196) . chr(137) => 'c',
            chr(196) . chr(138) => 'C',
            chr(196) . chr(139) => 'c',
            chr(196) . chr(140) => 'C',
            chr(196) . chr(141) => 'c',
            chr(196) . chr(142) => 'D',
            chr(196) . chr(143) => 'd',
            chr(196) . chr(144) => 'D',
            chr(196) . chr(145) => 'd',
            chr(196) . chr(146) => 'E',
            chr(196) . chr(147) => 'e',
            chr(196) . chr(148) => 'E',
            chr(196) . chr(149) => 'e',
            chr(196) . chr(150) => 'E',
            chr(196) . chr(151) => 'e',
            chr(196) . chr(152) => 'E',
            chr(196) . chr(153) => 'e',
            chr(196) . chr(154) => 'E',
            chr(196) . chr(155) => 'e',
            chr(196) . chr(156) => 'G',
            chr(196) . chr(157) => 'g',
            chr(196) . chr(158) => 'G',
            chr(196) . chr(159) => 'g',
            chr(196) . chr(160) => 'G',
            chr(196) . chr(161) => 'g',
            chr(196) . chr(162) => 'G',
            chr(196) . chr(163) => 'g',
            chr(196) . chr(164) => 'H',
            chr(196) . chr(165) => 'h',
            chr(196) . chr(166) => 'H',
            chr(196) . chr(167) => 'h',
            chr(196) . chr(168) => 'I',
            chr(196) . chr(169) => 'i',
            chr(196) . chr(170) => 'I',
            chr(196) . chr(171) => 'i',
            chr(196) . chr(172) => 'I',
            chr(196) . chr(173) => 'i',
            chr(196) . chr(174) => 'I',
            chr(196) . chr(175) => 'i',
            chr(196) . chr(176) => 'I',
            chr(196) . chr(177) => 'i',
            chr(196) . chr(178) => 'IJ',
            chr(196) . chr(179) => 'ij',
            chr(196) . chr(180) => 'J',
            chr(196) . chr(181) => 'j',
            chr(196) . chr(182) => 'K',
            chr(196) . chr(183) => 'k',
            chr(196) . chr(184) => 'k',
            chr(196) . chr(185) => 'L',
            chr(196) . chr(186) => 'l',
            chr(196) . chr(187) => 'L',
            chr(196) . chr(188) => 'l',
            chr(196) . chr(189) => 'L',
            chr(196) . chr(190) => 'l',
            chr(196) . chr(191) => 'L',
            chr(197) . chr(128) => 'l',
            chr(197) . chr(129) => 'L',
            chr(197) . chr(130) => 'l',
            chr(197) . chr(131) => 'N',
            chr(197) . chr(132) => 'n',
            chr(197) . chr(133) => 'N',
            chr(197) . chr(134) => 'n',
            chr(197) . chr(135) => 'N',
            chr(197) . chr(136) => 'n',
            chr(197) . chr(137) => 'N',
            chr(197) . chr(138) => 'n',
            chr(197) . chr(139) => 'N',
            chr(197) . chr(140) => 'O',
            chr(197) . chr(141) => 'o',
            chr(197) . chr(142) => 'O',
            chr(197) . chr(143) => 'o',
            chr(197) . chr(144) => 'O',
            chr(197) . chr(145) => 'o',
            chr(197) . chr(146) => 'OE',
            chr(197) . chr(147) => 'oe',
            chr(197) . chr(148) => 'R',
            chr(197) . chr(149) => 'r',
            chr(197) . chr(150) => 'R',
            chr(197) . chr(151) => 'r',
            chr(197) . chr(152) => 'R',
            chr(197) . chr(153) => 'r',
            chr(197) . chr(154) => 'S',
            chr(197) . chr(155) => 's',
            chr(197) . chr(156) => 'S',
            chr(197) . chr(157) => 's',
            chr(197) . chr(158) => 'S',
            chr(197) . chr(159) => 's',
            chr(197) . chr(160) => 'S',
            chr(197) . chr(161) => 's',
            chr(197) . chr(162) => 'T',
            chr(197) . chr(163) => 't',
            chr(197) . chr(164) => 'T',
            chr(197) . chr(165) => 't',
            chr(197) . chr(166) => 'T',
            chr(197) . chr(167) => 't',
            chr(197) . chr(168) => 'U',
            chr(197) . chr(169) => 'u',
            chr(197) . chr(170) => 'U',
            chr(197) . chr(171) => 'u',
            chr(197) . chr(172) => 'U',
            chr(197) . chr(173) => 'u',
            chr(197) . chr(174) => 'U',
            chr(197) . chr(175) => 'u',
            chr(197) . chr(176) => 'U',
            chr(197) . chr(177) => 'u',
            chr(197) . chr(178) => 'U',
            chr(197) . chr(179) => 'u',
            chr(197) . chr(180) => 'W',
            chr(197) . chr(181) => 'w',
            chr(197) . chr(182) => 'Y',
            chr(197) . chr(183) => 'y',
            chr(197) . chr(184) => 'Y',
            chr(197) . chr(185) => 'Z',
            chr(197) . chr(186) => 'z',
            chr(197) . chr(187) => 'Z',
            chr(197) . chr(188) => 'z',
            chr(197) . chr(189) => 'Z',
            chr(197) . chr(190) => 'z',
            chr(197) . chr(191) => 's'
        );

        $string = strtr($string, $chars);

        return $string;
    }

    /**
     * Finds the delimiter of a CSV file
     * @param     $filepath
     * @param int $checkLines
     *
     * @return mixed
     */
    private function findCsvDelimiter($filepath, $checkLines = 2)
    {
        $file = new \SplFileObject($filepath);
        $delimiters = array(
            ',',
            '\t',
            '\n',
            ';',
            '|',
            ':'
        );
        $results = array();
        $i = 0;
        while($file->valid() && $i <= $checkLines){
            $line = $file->fgets();
            foreach ($delimiters as $delimiter){
                $regExp = '/['.$delimiter.']/';
                $fields = preg_split($regExp, $line);
                if(count($fields) > 1){
                    if(!empty($results[$delimiter])){
                        $results[$delimiter]++;
                    } else {
                        $results[$delimiter] = 1;
                    }
                }
            }
            $i++;
        }
        $results = array_keys($results, max($results));
        return $results[0];
    }
} 