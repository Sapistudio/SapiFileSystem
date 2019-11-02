<?php
namespace SapiStudio\FileSystem\Parsers;

class XmlParser extends Parser
{
    private $xmlObject;
    private $simpleXml;
    
    /**
     * XmlParser::__construct()
     * 
     * @param mixed $data
     * @return
     */
    public function __construct($data)
    {
        $this->simpleXml = simplexml_load_string("<?xml version='1.0' standalone='yes'?><data>" .preg_replace('/^.+\n/', '', $data) . "</data>", 'SimpleXMLElement',LIBXML_NOCDATA);
        $this->xmlObject = $this->objectify($this->simpleXml);
    }
    
    /**
     * XmlParser::objectify()
     * 
     * @param mixed $value
     * @return
     */
    private function objectify($value)
    {
        $result = [];
        foreach ((array)$value as $key => $valueData)
            $result[$key] = (is_array($valueData) or is_object($valueData)) ? $this->objectify($valueData) : $valueData;
        return $result;
    }
    
    /**
     * XmlParser::toArray()
     * 
     * @return
     */
    public function toArray()
    {
        return (array)$this->xmlObject;
    }
    
    /**
     * XmlParser::toArray()
     * 
     * @return
     */
    public function toObject()
    {
        return $this->simpleXml;
    }
}
