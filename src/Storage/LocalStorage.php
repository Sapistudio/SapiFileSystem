<?php
/**
 * original class : codeguy/upload
 *
 * @author      Josh Lockhart <info@joshlockhart.com>
 * @copyright   2012 Josh Lockhart
 * @link        http://www.joshlockhart.com
 * @version     2.0.0
 *
 * MIT LICENSE
 */
namespace SapiStudio\FileSystem\Storage;

class LocalStorage
{
    protected $directory;
    protected $overwrite;

    public function __construct($directory, $overwrite = false)
    {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException('Directory does not exist');
        }
        if (!is_writable($directory)) {
            throw new \InvalidArgumentException('Directory is not writable');
        }
        $this->directory = rtrim($directory, '/') . DIRECTORY_SEPARATOR;
        $this->overwrite = (bool)$overwrite;
    }

    public function upload(FileInfo $fileInfo)
    {
        $destinationFile = $this->directory . $fileInfo->getNameWithExtension();
        if ($this->overwrite === false && file_exists($destinationFile) === true) {
            throw new \Exception('File already exists', $fileInfo);
        }

        if ($this->moveUploadedFile($fileInfo->getPathname(), $destinationFile) === false) {
            throw new \Exception('File could not be moved to final destination.', $fileInfo);
        }
    }

    protected function moveUploadedFile($source, $destination)
    {
        return move_uploaded_file($source, $destination);
    }
}
