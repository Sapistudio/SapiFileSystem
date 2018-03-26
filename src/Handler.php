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
        return array_merge(static::$filesystem->directories($dir), static::$filesystem->files($dir));
    }
    
    /**
     * Handler::dumpFile()
     * 
     * @param mixed $path
     * @param mixed $text
     * @return
     */
    public static function dumpFile($path,$text){
        //$this->chmod($path,0755);
        chmod($path, 0755);
        return static::$filesystem->put($path,$text);
    }
    
    /**
     * Handler::append()
     * 
     * @param mixed $path
     * @param mixed $data
     * @return
     */
    public static function append($path, $data)
    {
        return static::$filesystem->append($path,"\n".$data);
    }
    
    /**
     * Handler::appendJson()
     * 
     * @param mixed $path
     * @param mixed $text
     * @return
     */
    public static function appendJson($path,$entries=[]){
        return self::dumpJson($path,(new Collection(self::loadJson($path,true)))->concat($entries)->toArray());
    }
    
    /**
     * Handler::dumpJson()
     * 
     * @param mixed $path
     * @param mixed $text
     * @return
     */
    public static function dumpJson($path,$text){
        return self::dumpFile($path,json_encode(array_filter($text)));
    }
    
    /**
     * Handler::loadJson()
     * 
     * @param mixed $path
     * @param bool $toArray
     * @return
     */
    public static function loadJson($path,$toArray=false){
        try{
            return json_decode(static::$filesystem->get($path),$toArray);
        }catch(\Exception $e){
            return false;
        }
    }
}
