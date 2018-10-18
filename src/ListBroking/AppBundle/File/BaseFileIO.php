<?php

namespace ListBroking\AppBundle\File;

use Adclick\Components\GDPR\Service\ZipFileServiceInterface;

/**
 * ListBroking\AppBundle\File\BaseFileIO
 */
abstract class BaseFileIO extends BaseFile
{
    /**
     * @var string
     */
    protected $projectRootDir;

    /**
     * @var ZipFileServiceInterface
     */
    protected $zipService;

    /**
     * BaseFileIO constructor.
     *
     * @param string                  $projectRootDir
     * @param ZipFileServiceInterface $zipService
     */
    public function __construct(string $projectRootDir, ZipFileServiceInterface $zipService)
    {
        parent::__construct($projectRootDir);

        $this->zipService = $zipService;
    }

    /**
     * Zips a given file with optional password protection
     *
     * @param $path
     * @param $filename
     * @param $zipped
     *
     * @return array
     */
    protected function store($path, $filename, $zipped)
    {
        $fileInfo = [$path, $filename, ''];

        if ($zipped) {

            // replace original file by zipped
            $fileInfo = $this->zipService->compressFile($filename, true);
            unlink($filename);
        }

        return $fileInfo;
    }
}
