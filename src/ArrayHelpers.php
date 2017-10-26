<?php 
namespace SapiStudio\FileSystem;

use Illuminate\Support\Arr;

class ArrayHelpers {

	/**
	 * ArrayHelpers::isAssociative()
	 * 
	 * @param mixed $array
	 * @return
	 */
	public static function isAssociative($array) {
	    return array_keys($array) !== range(0, count($array) - 1);
	}

	/**
	 * ArrayHelpers::dotKeys()
	 * 
	 * @param mixed $data
	 * @return
	 */
	public static function dotKeys(array $data) {
		return array_keys(Arr::dot($data));
	}

	/**
	 * ArrayHelpers::dot()
	 * 
	 * @param mixed $data
	 * @return
	 */
	public static function dot(array $data) {
		return Arr::dot($data);
	}

	/**
	 * ArrayHelpers::set()
	 * 
	 * @param mixed $data
	 * @param mixed $key
	 * @param mixed $value
	 * @return
	 */
	public static function set(array &$data, $key, $value) {
		Arr::set($data, $key, $value);
	}
}