<?php
namespace SapiStudio\FileSystem\Parsers;

class XmlParser extends Parser
{
    private $xml;
    
    /**
     * XmlParser::objectify()
     * 
     * @param mixed $value
     * @return
     */
    private function objectify($value)
    {
        $temp = is_string($value) ? simplexml_load_string("<?xml version='1.0' standalone='yes'?><data>" .preg_replace('/^.+\n/', '', $value) . "</data>", 'SimpleXMLElement',LIBXML_NOCDATA) : $value;
        $result = [];
        foreach ((array )$temp as $key => $value)
        {
            $result[$key] = (is_array($value) or is_object($value)) ? $this->objectify($value) :
                $value;
        }
        return $result;
    }
    
    /**
     * XmlParser::__construct()
     * 
     * @param mixed $data
     * @return
     */
    public function __construct($data)
    {
        $this->xml = $this->objectify($data);
    }
    
    /**
     * XmlParser::toArray()
     * 
     * @return
     */
    public function toArray()
    {
        return (array )$this->xml;
    }
}
