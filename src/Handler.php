<?php
namespace SapiStudio\FileSystem;

use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem as IluminateFileSystem;

class Handler
{
    /** iluminate filesystem object*/
    protected static $filesystem = null;
    
    /**
     * Handler::__construct()
     * 
     * @return
     */
    public function __construct()
    {
        self::bootIluminateFileSystem();
    }

    /**
     * Handler::__callStatic()
     * 
     * @param mixed $method
     * @param mixed $parameters
     * @return
     */
    public static function __callStatic($method, $parameters)
    {
        self::bootIluminateFileSystem();
        return static::$filesystem->$method(...$parameters);
    }
    
    /**
     * Handler::bootIluminateFileSystem()
     * 
     * @return
     */
    protected static function bootIluminateFileSystem()
    {
        if (! isset(static::$filesystem)) {
            static::$filesystem = new IluminateFileSystem();
        }
    }
    
    /**
     * Handler::getFinder()
     * 
     * @return
     */
    public static function getFinder(){
        return Finder::create();
    }
    
    /**
     * Handler::createDir()
     * 
     * @param mixed $dir
     * @param integer $mkdirPermissions
     * @param bool $recursive
     * @return
     */
    public static function createDir($dir, $mkdirPermissions = 0755, $recursive=false){
        if (is_dir($dir))
            return true;
        static::$filesystem->makeDirectory($dir,$mkdirPermissions,$recursive);
    }
    
    /**
     * Handler::getDirectories()
     * 
     * @param mixed $dir
     * @return
     */
    public static function getDirectories($dir)
    {
        return static::$filesystem->directories($dir);
    }

    /**
     * Handler::getFiles()
     * 
     * @param mixed $dir
     * @return
     */
    public static function getFiles($dir)
    {
        return static::$filesystem->files($dir);
    }
    
    /**
     * Handler::getAllFiles()
     * 
     * @param mixed $dir
     * @param bool $returnfile
     * @return
     */
    public static function getAllFiles($dir,$returnfile = false)
    {
        if(!is_dir($dir))
            return false;
        $files = static::$filesystem->allFiles($dir);
        if(!$files)
            return false;
        foreach($files as $a=>$file)
            $return[] = ($returnfile) ? $file->getFilename() : $file->getRealPath();
        return $return;
    }

    /**
     * Handler::getDirectoriesAndFiles()
     * 
     * @param mixed $dir
     * @return
     */
    public static function getDirectoriesAndFiles($dir)
    {
        return array_merge(static::$filesystem->getDirectories($dir), static::$filesystem->getFiles($dir));
    }

    /**
     * Handler::deleteDir()
     * 
     * @param mixed $path
     * @return
     */
    public static function deleteDir($path)
    {
        static::$filesystem->deleteDirectory($path);
    }
    
    /**
     * Handler::deleteFile()
     * 
     * @param mixed $path
     * @return
     */
    public static function deleteFile($path)
    {
        return static::$filesystem->delete($path);
    }
    
    /**
     * Handler::copyFile()
     * 
     * @param mixed $path
     * @param mixed $target
     * @return
     */
    public static function copyFile($path, $target)
    {
        return static::$filesystem->copy($path, $target);
    }
    
    /**
     * Handler::moveFile()
     * 
     * @param mixed $path
     * @param mixed $target
     * @return
     */
    public static function moveFile($path, $target)
    {
        return static::$filesystem->move($path, $target);
    }
    
    /**
     * Handler::dumpFile()
     * 
     * @param mixed $path
     * @param mixed $text
     * @return
     */
    public static static function dumpFile($path,$text){
        //$this->chmod($path,0755);
        chmod($path, 0755);
        return static::$filesystem->put($path,$text);
    }
    
    /**
     * Handler::dumpJson()
     * 
     * @param mixed $path
     * @param mixed $text
     * @return
     */
    public static function dumpJson($path,$text){
        return static::$filesystem->dumpFile($path,json_encode($text));
    }
    
    /**
     * Handler::appendToFile()
     * 
     * @param mixed $path
     * @param mixed $data
     * @return
     */
    public static function appendToFile($path, $data)
    {
        return static::$filesystem->append($path,"\n".$data);
    }
    
    /**
     * Handler::loadJson()
     * 
     * @param mixed $path
     * @param bool $toArray
     * @return
     */
    public static function loadJson($path,$toArray=false){
        $file = static::$filesystem->get($path);
        return (!$file) ? false : json_decode($file,$toArray);
    }
    
    /**
     * Handler::loadFile()
     * 
     * @param mixed $path
     * @return
     */
    public static function loadFile($path){
        $file = new \SplFileInfo($path);
        if ($file->isFile()) {
            return file_get_contents($file->getPathname());
        }
        return false;
    }
}
