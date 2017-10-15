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
namespace SapiStudio\FileSystem\Upload;
use \SapiStudio\FileSystem\FileInfo;

class Handler implements \ArrayAccess, \IteratorAggregate, \Countable
{
    protected static $errorCodeMessages = [
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload'
    ];

    protected $storage;
    protected $beforeValidationCallback;
    protected $afterValidationCallback;
    protected $beforeUploadCallback;
    protected $afterUploadCallback;
    protected $objects      = [];
    protected $validations  = [];
    protected $errors       = [];
    protected $uploadedFile = null;

    /**
     * Handler::__construct()
     * 
     * @param mixed $key
     * @return
     */
    public function __construct($key)
    {
        if (ini_get('file_uploads') == false){
            throw new \RuntimeException('File uploads are disabled in your PHP.ini file');
        }
        if (isset($_FILES[$key]) === false) {
            throw new \Exception("Cannot find uploaded file(s) identified by key: $key");
        }
        if (is_array($_FILES[$key]['tmp_name']) === true) {
            foreach ($_FILES[$key]['tmp_name'] as $index => $tmpName) {
                if ($_FILES[$key]['error'][$index] !== UPLOAD_ERR_OK) {
                    $this->errors[] = sprintf('%s: %s',$_FILES[$key]['name'][$index],static::$errorCodeMessages[$_FILES[$key]['error'][$index]]);
                    continue;
                }
                $this->objects[] = FileInfo::createFactory($_FILES[$key]['tmp_name'][$index],$_FILES[$key]['name'][$index]);
            }
        }else{
            if($_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
                $this->errors[] = sprintf('%s: %s',$_FILES[$key]['name'],static::$errorCodeMessages[$_FILES[$key]['error']]);
            }
            $this->objects[] = FileInfo::createFactory($_FILES[$key]['tmp_name'],$_FILES[$key]['name']);
        }
    }

    /**
     * Handler::beforeValidate()
     * 
     * @param mixed $callable
     * @return
     */
    public function beforeValidate($callable)
    {
        if (is_object($callable) === false || method_exists($callable, '__invoke') === false) {
            throw new \Exception('Callback is not a Closure or invokable object.');
        }
        $this->beforeValidation = $callable;
        return $this;
    }

    /**
     * Handler::afterValidate()
     * 
     * @param mixed $callable
     * @return
     */
    public function afterValidate($callable)
    {
        if (is_object($callable) === false || method_exists($callable, '__invoke') === false) {
            throw new \Exception('Callback is not a Closure or invokable object.');
        }
        $this->afterValidation = $callable;
        return $this;
    }

    /**
     * Handler::beforeUpload()
     * 
     * @param mixed $callable
     * @return
     */
    public function beforeUpload($callable)
    {
        if (is_object($callable) === false || method_exists($callable, '__invoke') === false) {
            throw new \Exception('Callback is not a Closure or invokable object.');
        }
        $this->beforeUpload = $callable;
        return $this;
    }

    /**
     * Handler::afterUpload()
     * 
     * @param mixed $callable
     * @return
     */
    public function afterUpload($callable)
    {
        if (is_object($callable) === false || method_exists($callable, '__invoke') === false) {
            throw new \Exception('Callback is not a Closure or invokable object.');
        }
        $this->afterUpload = $callable;
        return $this;
    }

    /**
     * Handler::applyCallback()
     * 
     * @param mixed $callbackName
     * @param mixed $file
     * @return
     */
    protected function applyCallback($callbackName, FileInfo $file)
    {
        if (in_array($callbackName, array('beforeValidation', 'afterValidation', 'beforeUpload', 'afterUpload')) === true) {
            if (isset($this->$callbackName) === true) {
                call_user_func_array($this->$callbackName, array($file));
            }
        }
    }

    /**
     * Handler::addValidations()
     * 
     * @param mixed $validations
     * @return
     */
    public function addValidations(array $validations)
    {
        foreach ($validations as $validation) {
            $this->addValidation($validation);
        }
        return $this;
    }
    
    /**
     * Handler::setStorage()
     * 
     * @return void
     */
    public function setStorage($path){
        $this->storage = new \SapiStudio\FileSystem\Storage\LocalStorage($path,true);
        return $this;
    }
   
    /**
     * Handler::addValidation()
     * 
     * @param mixed $validation
     * @return
     */
    public function addValidation(ValidationInterface $validation)
    {
        $this->validations[] = $validation;
        return $this;
    }

    /**
     * Handler::getValidations()
     * 
     * @return
     */
    public function getValidations()
    {
        return $this->validations;
    }

    /**
     * Handler::isValid()
     * 
     * @return
     */
    public function isValid()
    {
        foreach ($this->objects as $fileInfo) {
            // Before validation callback
            $this->applyCallback('beforeValidation', $fileInfo);
            // Check is uploaded file
            if ($fileInfo->isUploadedFile() === false) {
                $this->errors[] = sprintf('%s: %s',$fileInfo->getNameWithExtension(),'Is not an uploaded file');
                continue;
            }
            // Apply user validations
            foreach ($this->validations as $validation) {
                try{
                    $validation->validate($fileInfo);
                } catch (\Exception $e) {
                    $this->errors[] = sprintf('%s: %s',$fileInfo->getNameWithExtension(),$e->getMessage());
                }
            }
            // After validation callback
            $this->applyCallback('afterValidation', $fileInfo);
        }

        return empty($this->errors);
    }

    /**
     * Handler::getErrors()
     * 
     * @return
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Handler::__call()
     * 
     * @param mixed $name
     * @param mixed $arguments
     * @return
     */
    public function __call($name, $arguments)
    {
        $count  = count($this->objects);
        $result = null;
        if ($count) {
            if ($count > 1) {
                $result = [];
                foreach ($this->objects as $object) {
                    $result[] = call_user_func_array([$object, $name], $arguments);
                }
            }else{
                $result = call_user_func_array([$this->objects[0], $name], $arguments);
            }
        }
        return $result;
    }
    
    /**
     * Handler::uploadDetails()
     * 
     * @return
     */
    public function uploadDetails(){
        return [
            'name'       => $this->getNameWithExtension(),
            'extension'  => $this->getExtension(),
            'mime'       => $this->getMimetype(),
            'size'       => $this->getSize(),
            'md5'        => $this->getMd5(),
            'dimensions' => $this->getDimensions()
        ];
    }
    
    /**
     * Handler::getUploadedFilePath()
     * 
     * @return
     */
    public function getUploadedFilePath(){
        return (!$this->uploadedFile) ? $file->getPathname() : $this->uploadedFile;
    }
    /**
     * Handler::upload()
     * 
     * @return
     */
    public function upload()
    {
        if ($this->isValid() === false){
            throw new \Exception('File validation failed');
        }
        foreach ($this->objects as $fileInfo) {
            $this->applyCallback('beforeUpload', $fileInfo);
            $this->uploadedFile = $this->storage->upload($fileInfo);
            $this->applyCallback('afterUpload', $fileInfo);
        }
        return true;
    }

    /********************************************************************************
     * Array Access Interface
     *******************************************************************************/

    /**
     * Handler::offsetExists()
     * 
     * @param mixed $offset
     * @return
     */
    public function offsetExists($offset)
    {
        return isset($this->objects[$offset]);
    }

    /**
     * Handler::offsetGet()
     * 
     * @param mixed $offset
     * @return
     */
    public function offsetGet($offset)
    {
        return isset($this->objects[$offset]) ? $this->objects[$offset] : null;
    }

    /**
     * Handler::offsetSet()
     * 
     * @param mixed $offset
     * @param mixed $value
     * @return
     */
    public function offsetSet($offset, $value)
    {
        $this->objects[$offset] = $value;
    }

    /**
     * Handler::offsetUnset()
     * 
     * @param mixed $offset
     * @return
     */
    public function offsetUnset($offset)
    {
        unset($this->objects[$offset]);
    }

    /********************************************************************************
     * Iterator Aggregate Interface
     *******************************************************************************/

    /**
     * Handler::getIterator()
     * 
     * @return
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->objects);
    }

    /********************************************************************************
     * Countable Interface
     *******************************************************************************/

    /**
     * Handler::count()
     * 
     * @return
     */
    public function count()
    {
        return count($this->objects);
    }
} 
