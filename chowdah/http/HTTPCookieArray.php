<?php

/**
 * HTTP Cookies Array
 * @package chowdah.http
 */

class HTTPCookieArray implements IteratorAggregate, ArrayAccess, Countable {
	protected $message;
	
	function __construct(HTTPMessage $message) {
		// save the message object
		$this->message = $message;
	}

	protected function getArray() {
		// parse HTTPRequest Cookie: header
		if ($this->message instanceof HTTPRequest)
			$cookies = http_parse_query(str_replace(';', '&', $this->message->getHeader('Cookie')));
		// parse HTTPResponse Set-Cookie: header
		if ($this->message instanceof HTTPResponse)
			$cookies = http_parse_query(implode('&', preg_replace('/\s*;.*$/', '',
			    (array) $this->message->getHeader('Set-Cookie'))));
		
		// return the array of cookies
		return $cookies;
	}
	
	public function offsetExists($offset) {
		// return whether the cookie exists
		$cookies = $this->getArray();
		return isset($cookies[$offset]);
	}

	public function offsetGet($offset) {
		// return the cookie
		$cookies = $this->getArray();
		return $cookies[$offset];
	}

	public function offsetSet($index, $newval, $params = '') {
		// validate the index
		if (!strlen($index))
			throw new Exception('HTTP cookie cannot be set without a key.');
		// delete any existing cookie
		$this->offsetUnset($index);	

		// add to HTTPRequest Cookie: header
		if ($this->message instanceof HTTPRequest)
			$this->message->setHeader('Cookie',
			    http_build_query(array($index => $newval) + $this->getArray(), '', ';'));
		// add to HTTPResponse Set-Cookie: header
		if ($this->message instanceof HTTPResponse)
			foreach (explode(';', http_build_query(array($index => $newval), '', ';')) as $value)
				$this->message->setHeader('Set-Cookie', $value . $params, false);
	}

	public function offsetUnset($index) {
		// check that this index exists
		if (!isset($this[$index]))
			return;

		// remove each entry
		foreach (explode(';', http_build_query(array($index => $this[$index]), '', ';')) as $item) {
			// remove from HTTPRequest Cookie: header
			if ($this->message instanceof HTTPRequest)
				$this->message->setHeader('Cookie',
				    trim(preg_replace('(;|^)\s*' . preg_quote($item) . '\s*(?=;|$)/', '',
				        (string) $this->message->getHeader('Cookie')), ';'));
			// remove from HTTPResponse Set-Cookie: header
			if ($this->message instanceof HTTPResponse)
				// remove the entry
				$this->message->setHeader('Set-Cookie',
				    preg_grep('/^\s*' . preg_quote($item) . '\s*(?=;|$)/',
				        (array) $this->message->getHeader('Set-Cookie'), PREG_GREP_INVERT));
		}
	}
	
	public function count() {
		return count($this->getArray());
	}
	
	public function getIterator() {
		return new ArrayIterator($this->getArray());
	}
}

?>