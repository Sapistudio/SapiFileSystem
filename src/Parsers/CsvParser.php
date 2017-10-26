<?php 

namespace SapiStudio\FileSystem\Parsers;

use InvalidArgumentException;
use League\Csv\Reader;
use SapiStudio\FileSystem\ArrayHelpers;

class CsvParser extends Parser {
    
	private $csvObject;
    private $_aHeaders  = [];
    private $hasHeaders = false;
    
    /**
     * CsvParser::__call()
     * 
     * @param mixed $name
     * @param mixed $arguments
     * @return
     */
    public function __call($name, $arguments)
    {
        $this->csvObject->$name(...$arguments);
        return $this;
    }
    
	/**
	 * CsvParser::__construct()
	 * 
	 * @param mixed $data
	 * @return
	 */
	public function __construct($data) {
		if (is_string($data)) {
			$this->csvObject = Reader::createFromString($data);
		} else {
			throw new InvalidArgumentException(
				'CsvParser only accepts (string) [csv] for $data.'
			);
		}
	}
    
    /**
     * CsvParser::csvMapping()
     * 
     * @param mixed $mappingData
     * @return
     */
    public function csvMapping($mappingData = []){
        $method     = ($this->hasHeaders) ? 'fetchAssoc' : 'fetchAll';
        $results    = $this->$method();
        $return     = [];
        foreach ($this->csvObject->$method() as $rowIndex => $rowData) {
            foreach($mappingData as $mappingFields=>$csvEntry){
                if(isset($rowData[$csvEntry]))
                    $return[$rowIndex][$mappingFields] = $rowData[$csvEntry];
            }
        }
        return $return;
    }
    
    /**
     * CsvParser::firstRowHeader()
     * 
     * @return
     */
    public function firstRowHeader()
    {
        if (!$this->_aHeaders){
            $this->_aHeaders = $this->fetchOne(0);
            $this->hasHeaders = true;
        }
        return $this;
    }
    
    /**
     * CsvParser::getHeaders()
     * 
     * @return
     */
    public function getHeaders()
    {
        return $this->_aHeaders;
    }
    
	/**
	 * CsvParser::toArray()
	 * 
	 * @return
	 */
	public function toArray() {
		$temp = $this->csvObject->jsonSerialize();
		$headings = $temp[0];
		$result = $headings;
		if (count($temp) > 1) {
			$result = [];
			for ($i = 1; $i < count($temp); ++$i) {
				$row = [];
				for ($j = 0; $j < count($headings); ++$j) {
					$row[$headings[$j]] = $temp[$i][$j];
				}
				$expanded = [];
				foreach ($row as $key => $value) {
					ArrayHelpers::set($expanded, $key, $value);
				}
				$result[] = $expanded;
			}
		}
		return $result;
	}
}