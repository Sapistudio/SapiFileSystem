<?php
namespace SapiStudio\FileSystem;

use \Illuminate\Config\Repository;
use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem as IluminateFileSystem;
use Illuminate\Support\Collection;

class Handler
{
    /** iluminate filesystem object*/
    protected static $filesystem = null;
    
    /** Handler::__construct() */
    public function __construct()
    {
        self::bootIluminateFileSystem();
    }

    /** Handler::__callStatic() */
    public static function __callStatic($method, $parameters)
    {
        self::bootIluminateFileSystem();
        return static::$filesystem->$method(...$parameters);
    }
    
    /** Handler::bootIluminateFileSystem() */
    protected static function bootIluminateFileSystem()
    {
        if (!isset(static::$filesystem)) {
            static::$filesystem = new IluminateFileSystem();
        }
    }
    
    /** Handler::getFinder()*/
    public static function getFinder(){
        return Finder::create();
    }
    
    /** Handler::createDir()*/
    public static function createDir($dir, $mkdirPermissions = 0755, $recursive=false){
        if (is_dir($dir))
            return true;
        static::$filesystem->makeDirectory($dir,$mkdirPermissions,$recursive);
    }

    /** Handler::getAllFiles() */
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

    /** Handler::getDirectoriesAndFiles()*/
    public static function getDirectoriesAndFiles($dir)
    {
        return array_merge(static::$filesystem->directories($dir), static::$filesystem->files($dir));
    }
    
    /** Handler::dumpFile()*/
    public static function dumpFile($path,$text){
        //$this->chmod($path,0755);
        chmod($path, 0755);
        return static::$filesystem->put($path,$text);
    }
    
    /** Handler::dumpJson() */
    public static function dumpJson($path,$text){
        return self::dumpFile($path,json_encode($text));
    }
    
    /** Handler::dumpJson() */
    public static function dumpToConfig($path,$configData){
        return self::dumpFile($path,self::var_export_config($configData));
    }
    
    /** Handler::append() */
    public static function append($path, $data)
    {
        return static::$filesystem->append($path,"\n".$data);
    }
    
    /** Handler::appendJson() */
    public static function appendJson($path,$entries=[]){
        return self::dumpJson($path,(new Collection(self::loadJson($path,true)))->concat($entries)->toArray());
    }
    
    /** Handler::loadJson() */
    public static function loadJson($path,$toArray=false){
        try{
            return json_decode(static::$filesystem->get($path),$toArray);
        }catch(\Exception $e){
            return ($toArray) ? [] : new \stdClass();
        }
    }
    
    /** Handler::loadJsonAsConfig()*/
    public static function loadJsonAsConfig($path){
        try{
            $jsonData = self::loadJson($path,true);
            return (new Repository(($jsonData) ? $jsonData : []));
        }catch(\Exception $e){
            return ($toArray) ? [] : new \stdClass();
        }
    }
    
    /** Handler::fileToArray() */
    public static function fileToArray($path,$separator="\n"){
        try{
            return explode($separator,static::$filesystem->get($path));
        }catch(\Exception $e){
            return [];
        }
    }
    
    /** Handler::arrayToFile() */
    public static function arrayToFile($path,$data=[]){
        try{
            return self::dumpFile($path,implode("\n",$data));
        }catch(\Exception $e){
            return [];
        }
    }
    
    /**
     * Handler::var_export_config()
     * convert PHP's array() syntax to PHP 5.4's short array syntax [],and modified result for config file
     * @return
     */
    public static function var_export_config($configData = [],$indent = 4) {
        $object = json_decode(str_replace(['(', ')'], ['&#40', '&#41'], json_encode($configData)), true);
        $export = str_replace(['array (', ')', '&#40', '&#41'], ['[', ']', '(', ')'], var_export($object, true));
        $export = preg_replace("/ => \n[^\S\n]*\[/m", ' => [', $export);
        $export = preg_replace("/ => \[\n[^\S\n]*\]/m", ' => []', $export);
        $spaces = str_repeat(' ', $indent);
        $export = preg_replace("/([ ]{2})(?![^ ])/m", $spaces, $export);
        $export = preg_replace("/^([ ]{2})/m", $spaces, $export);
        $export = str_ireplace("'false'", "false", $export);
        $export = str_ireplace("'true'", "true", $export);
        return '<?php '."\n".'return '.$export.';';
    }
}
