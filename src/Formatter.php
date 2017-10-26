<?php 
namespace SapiStudio\FileSystem;

use InvalidArgumentException;
use SapiStudio\FileSystem\Parsers\ArrayParser;
use SapiStudio\FileSystem\Parsers\CsvParser;
use SapiStudio\FileSystem\Parsers\JsonParser;
use SapiStudio\FileSystem\Parsers\XmlParser;
use SapiStudio\FileSystem\Handler as FileHandler;

class Formatter {
	/**
	 * Add class constants that help define input format
	 */
	const CSV  = 'csv';
	const JSON = 'json';
	const XML  = 'xml';
	const ARR  = 'array';
	const YAML = 'yaml';

	private static $supportedTypes = [self::CSV, self::JSON, self::XML, self::ARR, self::YAML];
	private $parser;
    
    /**
     * Formatter::__construct()
     * 
     * @param mixed $parser
     * @return void
     */
    private function __construct($parser) {
		$this->parser = $parser;
	}
    
    /**
     * Formatter::__call()
     * 
     * @param mixed $name
     * @param mixed $arguments
     * @return
     */
    public function __call($name, $arguments)
    {
        return $this->parser->$name(...$arguments);
    }
        
    /**
     * Formatter::__callStatic()
     * 
     * @param mixed $name
     * @param mixed $arguments
     * @return
     */
    public static function __callStatic($name, $arguments)
    {
        $name = strtolower($name);
        if (in_array($name, self::$supportedTypes)) {
            return self::make($name,...$arguments);
        }
        return false;
    }
    
    /**
	 * Formatter::make()
	 * 
	 * @param mixed $type
	 * @param mixed $data
	 * @return
	 */
	public static function make($type,$data) {
		if (in_array($type, self::$supportedTypes)) {
			$parser = null;
            $data   = (is_file($data)) ? FileHandler::get($data) : $data;
			switch ($type) {
				case self::CSV:
					$parser = new CsvParser($data);
					break;
				case self::JSON:
					$parser = new JsonParser($data);
					break;
				case self::XML:
					$parser = new XmlParser($data);
					break;
				case self::ARR:
					$parser = new ArrayParser($data);
					break;
			}
			return new Formatter($parser);
		}
		throw new InvalidArgumentException(
			'make function only accepts [csv, json, xml, array] for $type but ' . $type . ' was provided.'
		);
	}
}