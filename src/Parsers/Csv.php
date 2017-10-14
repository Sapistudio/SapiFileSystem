<?php

namespace SapiStudio\FileSystem\Parsers;
use SplFileObject;

class Csv extends \SplFileObject
{
    private $filename;
    private $_aHeaders = [];
    
    public static function create($filename, $settings = [])
    {
        return new static ($filename,$settings);
    }
    
    public function __construct($filename, $settings = [])
    {
        parent::__construct($filename, 'r');
        $this->setFlags(self::SKIP_EMPTY | self::READ_AHEAD | self::DROP_NEW_LINE | self::READ_CSV);
        $this->setCsvControl($settings['delimiter']);
        $this->filename = $filename;
    }
    
    public function csvMapping($mappingData = []){
        $mappingData = array_filter($mappingData);
        foreach ($this as $k => $line) {
            $csvValues = $this->current();
            foreach($mappingData as $mappingFields=>$csvEntry){
                $return[$this->key()][$mappingFields] = (!isset($this->_aHeadersReverse[$csvEntry])) ? $csvEntry : $csvValues[$this->_aHeadersReverse[$csvEntry]];
            }
        }
        return $return;
    }
    
    public function firstRowHeader($bFirstRowHeader = true)
    {
        parent::rewind();
        if (!$this->_aHeaders){
            $this->_aHeaders        = array_filter(parent::current());
            $this->_aHeadersReverse = array_combine(array_values($this->_aHeaders),array_keys($this->_aHeaders));
            parent::next();
        }
        return $this;
    }

    public function getHeaders()
    {
        return $this->_aHeaders;
    }
    
    public function rewind()
    {
        parent::rewind();
        if (count($this->_aHeaders) ) {
            parent::next();
        }
    }
    
    public function totalLines(){
        $this->seek(PHP_INT_MAX);
        return $this->key();
    }
    
    public function current()
    {
        if (count($this->_aHeaders)){
            //return array_combine($this->_aHeaders, parent::current());
        }
        return parent::current();
    }
}