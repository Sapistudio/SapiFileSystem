<?php

namespace SapiStudio\FileSystem\Parsers;
use SplFileObject;

class Csv extends SplFileObject
{
    const   OPEN_READ_ONLY = "r",
        OPEN_READ_WRITE_PLUS = "r+",
        OPEN_WRITE_ONLY_CREATE = "w",
        OPEN_WRITE_READ_CREATE = "w+",
        OPEN_READ_ONLY_END_FILE = "a",
        OPEN_READ_WRITE_END_FILE = "a+",
        OPEN_CREATE_WRITE = "x",
        OPEN_CREATE_WRITE_READ = "x+",
        OPEN_WRITE_ONLY_CREATE_UNTRUNCATE = "c",
        OPEN_WRITE_READ_CREATE_UNTRUNCATE = "c+";
   
    private $filename;
    private $settings = [
        'csv' => [
            "delimiter" => '|',
            "enclosure" => '"',
            "escape"    => '"'
        ]
    ];
    
    public function __construct($filename, $openStreamType = null, $settings = [])
    {
        if ($openStreamType == null) {
            $openStreamType = self::OPEN_READ_ONLY_END_FILE;
        }
        if ($settings) {
            $this->settings = $settings;
        }
        parent::__construct($filename, $openStreamType);
        parent::setCsvControl($this->settings['csv']['delimiter'],$this->settings['csv']['enclosure'],$this->settings['csv']['escape']);
        $this->filename = $filename;
    }
    
    public static function create($filename, $openStreamType = null, $settings = [])
    {
        return new static ($filename, $openStreamType, $settings);
    }
    
    public function firstRowHeader($bFirstRowHeader = true)
    {
        parent::rewind();
        if ($bFirstRowHeader == true) {
            $this->_aHeaders = parent::current();
            parent::next();
        }
        else {
            $this->_aHeaders = [];
        }
    }
    
    public function rewind()
    {
        parent::rewind();
        if ( count($this->_aHeaders) ) {
            parent::next();
        }
    }
    
    public function current()
    {
        if ( count($this->_aHeaders) ) {
            return array_combine($this->_aHeaders, parent::current());
        }
        return parent::current();
    }
    public function seek($iLinePos)
    {
        parent::seek(++$iLinePos);
    }
    
    public function addHeader(array $inputHeader = [])
    {
        return self::fputcsv(
            $inputHeader,
            $this->settings['csv']['delimiter'],
            $this->settings['csv']['enclosure']
        );
    }
    
    public function writeCollection($collection = [])
    {
        foreach ($collection as $lineItems) {
            $lineItems = $this->normalizeData($lineItems);
            self::writeContentLine($lineItems);
        }
    }
    
    public function writeContentLine(array  $lineItems = [])
    {
        $lineItems = $this->normalizeData($lineItems);
        return self::fputcsv($lineItems,
            $this->settings['csv']['delimiter'],
            $this->settings['csv']['enclosure']
        );
    }
}