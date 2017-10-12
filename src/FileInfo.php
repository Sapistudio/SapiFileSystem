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
 *
 */
namespace SapiStudio\FileSystem;

class FileInfo extends \SplFileInfo
{
    protected $name;
    protected $extension;
    protected $mimetype;

    /**
     * FileInfo::__construct()
     * 
     * @param mixed $filePathname
     * @param mixed $newName
     * @return
     */
    public function __construct($filePathname, $newName = null)
    {
        $desiredName = is_null($newName) ? $filePathname : $newName;
        $this->setName(pathinfo($desiredName, PATHINFO_FILENAME));
        $this->setExtension(pathinfo($desiredName, PATHINFO_EXTENSION));
        parent::__construct($filePathname);
    }

    /**
     * FileInfo::getName()
     * 
     * @return
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * FileInfo::setName()
     * 
     * @param mixed $name
     * @return
     */
    public function setName($name)
    {
        $name = preg_replace("/([^\w\s\d\-_~,;:\[\]\(\).]|[\.]{2,})/", "", $name);
        $name = basename($name);
        $this->name = $name;
        return $this;
    }

    /**
     * FileInfo::getExtension()
     * 
     * @return
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * FileInfo::setExtension()
     * 
     * @param mixed $extension
     * @return
     */
    public function setExtension($extension)
    {
        $this->extension = strtolower($extension);
        return $this;
    }

    /**
     * FileInfo::getNameWithExtension()
     * 
     * @return
     */
    public function getNameWithExtension()
    {
        return $this->extension === '' ? $this->name : sprintf('%s.%s', $this->name, $this->extension);
    }

    /**
     * FileInfo::getMimetype()
     * 
     * @return
     */
    public function getMimetype()
    {
        if (isset($this->mimetype) === false) {
            $finfo = new \finfo(FILEINFO_MIME);
            $mimetype = $finfo->file($this->getPathname());
            $mimetypeParts = preg_split('/\s*[;,]\s*/', $mimetype);
            $this->mimetype = strtolower($mimetypeParts[0]);
            unset($finfo);
        }
        return $this->mimetype;
    }

    /**
     * FileInfo::getMd5()
     * 
     * @return
     */
    public function getMd5()
    {
        return md5_file($this->getPathname());
    }

    /**
     * FileInfo::getHash()
     * 
     * @param string $algorithm
     * @return
     */
    public function getHash($algorithm = 'md5')
    {
        return hash_file($algorithm, $this->getPathname());
    }

    /**
     * FileInfo::getDimensions()
     * 
     * @return
     */
    public function getDimensions()
    {
        list($width, $height) = getimagesize($this->getPathname());
        return [
            'width' => $width,
            'height' => $height
        ];
    }

    /**
     * FileInfo::isUploadedFile()
     * 
     * @return
     */
    public function isUploadedFile()
    {
        return is_uploaded_file($this->getPathname());
    }

    /**
     * FileInfo::createFactory()
     * 
     * @param mixed $tmpName
     * @param mixed $name
     * @return
     */
    public static function createFactory($tmpName, $name = null) {
        return new static($tmpName, $name);
    }
}