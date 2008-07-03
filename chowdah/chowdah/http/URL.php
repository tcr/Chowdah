<?php

/**
 * URL Class
 * @package chowdah.http
 */

class URL {
	protected $components = array();
	
	function __construct($components = null) {
		// set the components
		if ($components)
			$this->setComponents($components);
	}
	
	//----------------------------------------------------------------------
	// setters and getters
	//----------------------------------------------------------------------
	
	public function serialize() {
		// return a serialized URL
		return ($this->scheme ? $this->scheme . '://' : '') .
		    ($this->user ? urlencode($this->user) .
		        ($this->pass ? ':' . urlencode($this->pass) : '') . '@' : '') .
		    $this->host . ($this->port && $this->port != '80' ? ':' . $this->port : '') . $this->path .
		    ($this->query ? '?' . $this->query : '') . ($this->fragment ? '#' . $this->fragment : '');
	}
	
	//----------------------------------------------------------------------
	// components
	//----------------------------------------------------------------------
	
	public function getComponents() {
		// return the components array
		return $this->components;
	}

	public function setComponents($components, $clear = false) {
		// clear components if specified
		if ($clear)
			$this->components = array();
		// validate and normalize components
		foreach ((array) $components as $key => $value)
			if (in_array($key, array('scheme', 'user', 'pass',
			    'host', 'port', 'path', 'query', 'fragment')))
				$this->components[$key] = ($value === null ? null : (string) $value);
	}
	
	//----------------------------------------------------------------------
	// url construction
	//----------------------------------------------------------------------
	
	static public function create($components) {
		// build a URL from components
		return new URL($components);
	}

	static public function parse($url) {
		// build a componentized url
		if (($components = @parse_url($url)) === false)
			return false;
		return new URL($components);
	}

	//----------------------------------------------------------------------
	// magic methods
	//----------------------------------------------------------------------
	
	function __get($key) {
		return isset($this->components[$key]) ? $this->components[$key] : false;
	}

	function __set($key, $value) {
		$this->setComponents(array($key => $value));
	}
	
	function __toString() {
		return $this->serialize();
	}
}

?>