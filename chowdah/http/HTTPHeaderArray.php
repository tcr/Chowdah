<?php

/**
 * HTTP Headers Array
 * @package chowdah.http
 */

class HTTPHeaderArray extends ArrayObject {
	public function offsetExists($index) {
		return parent::offsetExists(strtolower($index));
	}

	public function offsetGet($index) {
		return parent::offsetGet(strtolower($index));
	}

	public function offsetSet($index, $newval, $overwrite = true) {
		// validate the index
		if (!strlen($index))
			throw new Exception('HTTP header cannot be set without a key.');
		
		// add to existing value if specified
		if (!$overwrite && isset($this[$index]))
			$newval = array_merge((array) $newval, (array) $this[$index]);
		// format the value
		if (is_array($newval) && count($newval) == 1)
			$newval = preg_replace('/\n(\S)/', "\n \\1", (string) $newval[0]);
		else if (is_array($newval))
			foreach ($newval as &$value)
				$value = preg_replace('/\n(\S)/', "\n \\1", (string) $value);
		else
			$newval = preg_replace('/\n(\S)/', "\n \\1", (string) $newval);
		// set the entry
		parent::offsetSet(strtolower($index), $newval);
			
	}

	public function offsetUnset($index) {
		parent::offsetUnset(strtolower($index));
	}
}

?>