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

    /**
     * Constructor
     *
     * @param  string                    $key     The $_FILES[] key
     * @throws \RuntimeException                  If file uploads are disabled in the php.ini file
     * @throws \InvalidArgumentException          If $_FILES[] does not contain key
     */
    public function __construct($key)
    {
        if (ini_get('file_uploads') == false) {
            throw new \RuntimeException('File uploads are disabled in your PHP.ini file');
        }
        if (isset($_FILES[$key]) === false) {
            throw new \InvalidArgumentException("Cannot find uploaded file(s) identified by key: $key");
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
            if ($_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
                $this->errors[] = sprintf('%s: %s',$_FILES[$key]['name'],static::$errorCodeMessages[$_FILES[$key]['error']]);
            }
            $this->objects[] = FileInfo::createFactory($_FILES[$key]['tmp_name'],$_FILES[$key]['name']);
        }
    }

    public function beforeValidate($callable)
    {
        if (is_object($callable) === false || method_exists($callable, '__invoke') === false) {
            throw new \InvalidArgumentException('Callback is not a Closure or invokable object.');
        }
        $this->beforeValidation = $callable;
        return $this;
    }

    public function afterValidate($callable)
    {
        if (is_object($callable) === false || method_exists($callable, '__invoke') === false) {
            throw new \InvalidArgumentException('Callback is not a Closure or invokable object.');
        }
        $this->afterValidation = $callable;
        return $this;
    }

    public function beforeUpload($callable)
    {
        if (is_object($callable) === false || method_exists($callable, '__invoke') === false) {
            throw new \InvalidArgumentException('Callback is not a Closure or invokable object.');
        }
        $this->beforeUpload = $callable;
        return $this;
    }

    public function afterUpload($callable)
    {
        if (is_object($callable) === false || method_exists($callable, '__invoke') === false) {
            throw new \InvalidArgumentException('Callback is not a Closure or invokable object.');
        }
        $this->afterUpload = $callable;
        return $this;
    }

    protected function applyCallback($callbackName, FileInfo $file)
    {
        if (in_array($callbackName, array('beforeValidation', 'afterValidation', 'beforeUpload', 'afterUpload')) === true) {
            if (isset($this->$callbackName) === true) {
                call_user_func_array($this->$callbackName, array($file));
            }
        }
    }

    public function addValidations(array $validations)
    {
        foreach ($validations as $validation) {
            $this->addValidation($validation);
        }
        return $this;
    }

   
    public function addValidation(ValidationInterface $validation)
    {
        $this->validations[] = $validation;
        return $this;
    }

    public function getValidations()
    {
        return $this->validations;
    }

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
                try {
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

    public function getErrors()
    {
        return $this->errors;
    }

    public function __call($name, $arguments)
    {
        $count  = count($this->objects);
        $result = null;
        if ($count) {
            if ($count > 1) {
                $result = array();
                foreach ($this->objects as $object) {
                    $result[] = call_user_func_array(array($object, $name), $arguments);
                }
            } else{
                $result = call_user_func_array(array($this->objects[0], $name), $arguments);
            }
        }
        return $result;
    }

    public function upload()
    {
        if ($this->isValid() === false) {
            throw new \Exception('File validation failed');
        }
        foreach ($this->objects as $fileInfo) {
            $this->applyCallback('beforeUpload', $fileInfo);
            $this->storage->upload($fileInfo);
            $this->applyCallback('afterUpload', $fileInfo);
        }
        return true;
    }

    /********************************************************************************
     * Array Access Interface
     *******************************************************************************/

    public function offsetExists($offset)
    {
        return isset($this->objects[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->objects[$offset]) ? $this->objects[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->objects[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->objects[$offset]);
    }

    /********************************************************************************
     * Iterator Aggregate Interface
     *******************************************************************************/

    public function getIterator()
    {
        return new \ArrayIterator($this->objects);
    }

    /********************************************************************************
     * Countable Interface
     *******************************************************************************/

    public function count()
    {
        return count($this->objects);
    }
}