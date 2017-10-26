<?php 
namespace SapiStudio\FileSystem\Parsers;

use Illuminate\Support\Str;
use SapiStudio\FileSystem\ArrayHelpers;


abstract class Parser {

	/**
	 * Constructor is used to initialize the parser
	 *
	 * @param mixed $data The input sharing a type with the parser
	 */
	abstract public function __construct($data);

	/**
	 * Used to retrieve a (php) array representation of the data encapsulated within our Parser.
	 *
	 */
	abstract public function toArray();

	/**
	 * Parser::toJson()
	 * 
	 * @return
	 */
	public function toJson() {
		return json_encode($this->toArray());
	}

	/**
	 * Parser::xmlify()
	 * 
	 * @param mixed $data
	 * @param mixed $structure
	 * @param string $basenode
	 * @return
	 */
	private function xmlify($data, $structure = null, $basenode = 'xml') {
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1) {
			ini_set('zend.ze1_compatibility_mode', 0);
		}

		if ($structure == null) {
			$structure = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$basenode />");
		}

		// Force it to be something useful
		if (!is_array($data) && !is_object($data)) {
			$data = (array) $data;
		}

		foreach ($data as $key => $value) {
			// convert our booleans to 0/1 integer values so they are
			// not converted to blanks.
			if (is_bool($value)) {
				$value = (int) $value;
			}

			// no numeric keys in our xml please!
			if (is_numeric($key)) {
				// make string key...
				$key = (Str::singular($basenode) != $basenode) ? Str::singular($basenode) : 'item';
			}

			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z_\-0-9]/i', '', $key);

			// if there is another array found recrusively call this function
			if (is_array($value) or is_object($value)) {
				$node = $structure->addChild($key);

				// recursive call if value is not empty
				if (!empty($value)) {
					$this->xmlify($value, $node, $key);
				}
			} else {
				// add single node.
				$value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, "UTF-8");

				$structure->addChild($key, $value);
			}
		}

		// pass back as string. or simple xml object if you want!
		return $structure->asXML();
	}

	
	/**
	 * Parser::toXml()
	 * 
	 * @param string $baseNode
	 * @return
	 */
	public function toXml($baseNode = 'xml') {
		return $this->xmlify($this->toArray(), null, $baseNode);
	}

	/**
	 * Parser::csvify()
	 * 
	 * @param mixed $data
	 * @return
	 */
	private function csvify($data) {
		$results = [];
		foreach ($data as $row) {
			$results[] = array_values(ArrayHelpers::dot($row));
		}
		return $results;
	}

	/**
	 * Parser::toCsv()
	 * 
	 * @param string $newline
	 * @param string $delimiter
	 * @param string $enclosure
	 * @param string $escape
	 * @return
	 */
	public function toCsv($newline = "\n", $delimiter = ",", $enclosure = '"', $escape = "\\") {
		$data = $this->toArray();

		if (ArrayHelpers::isAssociative($data) || !is_array($data[0])) {
			$data = [$data];
		}

		$escaper = function($items) use($enclosure, $escape) {
			return array_map(function($item) use($enclosure, $escape) {
				return str_replace($enclosure, $escape.$enclosure, $item);
			}, $items);
		};

		$headings = ArrayHelpers::dotKeys($data[0]);
		$result = [];

		foreach ($data as $row) {
			$result[] = array_values(ArrayHelpers::dot($row));
		}

		$data = $result;

		$output = $enclosure.implode($enclosure.$delimiter.$enclosure, $escaper($headings)).$enclosure.$newline;

		foreach ($data as $row)
		{
			$output .= $enclosure.implode($enclosure.$delimiter.$enclosure, $escaper((array) $row)).$enclosure.$newline;
		}

		return rtrim($output, $newline);
	}
}