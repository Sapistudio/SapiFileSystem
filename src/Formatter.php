<?php 
namespace SapiStudio\FileSystem;

use InvalidArgumentException;
use SapiStudio\FileSystem\Parsers\ArrayParser;
use SapiStudio\FileSystem\Parsers\CsvParser;
use SapiStudio\FileSystem\Parsers\JsonParser;
use SapiStudio\FileSystem\Parsers\XmlParser;

class Formatter{
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
	 * Make: Returns an instance of formatter initialized with data and type
	 *
	 * @param mixed $data The data that formatter should parse
	 * @param string $type The type of data formatter is expected to parse
	 *
	 * @return Formatter
	 */
	public static function make($data, $type) {
		if (in_array($type, self::$supportedTypes)) {
			$parser = null;
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
			return new Formatter($parser, $type);
		}
		throw new InvalidArgumentException(
			'make function only accepts [csv, json, xml, array] for $type but ' . $type . ' was provided.'
		);
	}

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
	 * Formatter::toJson()
	 * 
	 * @return
	 */
	public function toJson() {
		return $this->parser->toJson();
	}

	/**
	 * Formatter::toArray()
	 * 
	 * @return
	 */
	public function toArray() {
		return $this->parser->toArray();
	}

	/**
	 * Formatter::toYaml()
	 * 
	 * @return
	 */
	public function toYaml() {
		return $this->parser->toYaml();
	}

	/**
	 * Formatter::toXml()
	 * 
	 * @param string $baseNode
	 * @return
	 */
	public function toXml($baseNode = 'xml') {
		return $this->parser->toXml($baseNode);
	}

	public function toCsv() {
		return $this->parser->toCsv();
	}
}