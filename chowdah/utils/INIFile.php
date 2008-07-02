<?php

/**
 * INI File reader/writer
 * @package chowdah.utils
 */

class INIFile
{
	protected $data = array('' => array());

	function __construct($path = null)
	{
		// load file
		if ($path)
			$this->load($path);
	}
	
	public function load($path) {
		// parse file
		return $this->loadString(file_get_contents($path));
	}

	public function loadString($data) {
		// parse file
		$this->data = array('' => array());
		$currentSection = '';
		foreach (preg_split('/\r\n?|\r?\n/', $data) as $line)
		{
			// normalize and parse line
			if (preg_match('/^\s*\[\s*(.*)\s*\]\s*$/', $line, $matches))
			{
				// check section header
				$currentSection = $matches[1];
			}
			else if (preg_match('/^\s*([^;\s].*?)\s*=\s*([^\s].*?)$/', $line, $matches))
			{
				// get key
				$key = preg_replace('/\[\]$/', '', $matches[1]);
				$isArray = preg_match('/\[\]$/', $matches[1]);
				
				// parse value
				preg_match('/^"(?:\\.|[^"])*"|^\'(?:[^\']|\\.)*\'|^[^;]+?\s*(?=;|$)/', $matches[2], $matches);
				$value = preg_replace('/^(["\'])(.*?)\1?$/', '\2', stripslashes($matches[0]));
				// parse data types
				if (is_numeric($value))
					$value = (float) $value;
				else if (strtolower($value) == 'true')
					$value = true;
				else if (strtolower($value) == 'false')
					$value = false;
				
				// parse key heirarchy
				$section =& $this->data[$currentSection];
				foreach (explode('.', $key) as $level) {
					if (!is_array($section[$level]))
						$section[$level] = array();
					$section =& $section[$level];
				}
				// set value on key
				$isArray ? $section[] = $value : $section = $value;
			}
		}
	}
	
	public function save($path) {
		// save file
		return file_put_contents($path, $this->saveString());
	}
	
	public function saveString()
	{
		// get sections list (beginning with globals)
		$sections = array_unique(array_merge(array(''), array_keys($this->data)));
		ksort($sections);
		
		// serialize sections
		$ini = array();
		foreach ($sections as $section) {
			// write sections
			if ($section != '')
				$ini[] = '[' . $section . ']';
				
			// serialize value array
			$ini = array_merge($ini, $this->serializeArray($this->data[$section]));
			$ini[] = '';
		}	
		
		// write file
		return implode("\r\n", $ini);
	}
	
	protected function serializeArray($array, $prefix = '') {
		// serialize value array
		$ini = array();
		ksort($array);
		foreach ($array as $key => $value)
		{
			// parse data types
			if ($value === true)
				$value = 'true';
			else if ($value === false)
				$value = 'false';
			else if (is_string($value))
				$value = '"' . addslashes($value) . '"';
				
			// check value type
			if (!is_array($value))
			{
				// serialize value
				if (is_numeric($key))
					$ini[] = $prefix . '[] = ' . $value;
				else 
					$ini[] = ($prefix ? $prefix . '.' : '') . $key . ' = ' . $value;
			}
			else
			{
				// serialize array
				$ini = array_merge($ini, $this->serializeArray($value, ($prefix ? $prefix . '.' : '') . $key));
			}
		}
		return $ini;
	}
	
	//----------------------------------------------------------------------
	// data accessors
	//----------------------------------------------------------------------
	
	public function getValue($name, $section = '', $parseConstants = true)
	{
		// return value (and parse constants)
		$value = $this->data[$section][$name];
		if ($parseConstants)
			$value = preg_replace('/\{([^}]+)\}/e', "constant('\\1')", $value);
		return $value;
	}
	
	public function setValue($name, $value, $section = '')
	{
		// add new entry
		return $this->data[$section][$name] = $value;
	}
	
	public function deleteValue($name, $section = '')
	{
		// delete entry
		unset($this->data[$section][$name]);
	}
	
	function __get($name)
	{
		switch ($name)
		{
			case 'data': return $this->data;
		}
	}
}

?>