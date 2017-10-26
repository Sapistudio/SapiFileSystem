<?php 
namespace SapiStudio\FileSystem\Parsers;

use InvalidArgumentException;

class ArrayParser extends Parser {

	private $array;

	/**
	 * ArrayParser::__construct()
	 * 
	 * @param mixed $data
	 * @return
	 */
	public function __construct($data) {
		if (is_string($data)) {
			$data = unserialize($data);
		}

		if (is_array($data) || is_object($data)) {
			$this->array = (array) $data;
		} else {
			throw new InvalidArgumentException(
				'ArrayParser only accepts (optionally serialized) [object, array] for $data.'
			);
		}
	}

	/**
	 * ArrayParser::toArray()
	 * 
	 * @return
	 */
	public function toArray() {
		return $this->array;
	}
}