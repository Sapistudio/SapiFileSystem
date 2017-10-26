<?php 

namespace SapiStudio\FileSystem\Parsers;

class JsonParser extends Parser {

	private $json;

	/**
	 * JsonParser::__construct()
	 * 
	 * @param mixed $data
	 * @return
	 */
	public function __construct($data) {
		$this->json = json_decode(trim($data));
	}

	/**
	 * JsonParser::toArray()
	 * 
	 * @return
	 */
	public function toArray() {
		return (array) $this->json;
	}
}