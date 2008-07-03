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
		// parse data
		$this->data = array('' => array());
		$currentSection = '';
		foreach (preg_split('/\r\n?|\r?\n/', $data) as $line)
		{
			// parse line
			if (preg_match('/^\s*\[\s*(.*)\s*\]\s*$/', $line, $matches))
			{
				// section header
				$currentSection = $matches[1];
			}
			else if (preg_match('/^\s*([^;\s].*?)\s*=\s*([^\s].*?)$/', $line, $matches))
			{
				// parse value
				if (preg_match('/^"(?:\\.|[^"])*"|^\'(?:[^\']|\\.)*\'/', $matches[2], $value))
					$value = stripslashes(substr($value[0], 1, -1));
				else 
					$value = preg_replace('/^["\']|\s*;.*$/', '', $matches[2]);
				// parse data types
				if (is_numeric($value))
					$value = (float) $value;
				else if (strtolower($value) == 'true')
					$value = true;
				else if (strtolower($value) == 'false')
					$value = false;
				
				// set value
				$name = $matches[1];
				$section =& $this->parseVariableName($name, $currentSection, true);
				$section[$name] = $value;
			}
		}
	}
	
	public function save($path)
	{
		// save file
		return file_put_contents($path, $this->saveString());
	}
	
	public function saveString()
	{
		// get sections list (beginning with globals)
		ksort($this->data);
		$sections = array_unique(array_merge(array(''), array_keys($this->data)));
		
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
	
	protected function serializeArray($array, $prefix = '')
	{
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
				
			// serialize value
			if (!is_array($value) && !is_numeric($key))
				$ini[] = ($prefix ? $prefix . '.' : '') . $key . ' = ' . $value;
			else if (!is_array($value))
				$ini[] = $prefix . '[] = ' . $value;
			else
				$ini = array_merge($ini, $this->serializeArray($value, ($prefix ? $prefix . '.' : '') . $key));
		}
		return $ini;
	}
	
	protected function &parseVariableName(&$name, $section = '', $create = false)
	{
		// parse name
		$levels = explode('.', $name);
		// check array
		if (substr($name, -2, 2) == '[]')
			$name = '[]';
		else
			$name = array_pop($levels);
		
		// climb section heirarchy
		$section =& $this->data[$section];
		foreach ($levels as $level)
		{
			if (!is_array($section[$level]) && !$create)
				return false;
			else if (!is_array($section[$level]))
				$section[$level] = array();
			$section =& $section[$level];
		}
		
		// get array key
		if ($name == '[]')
			$name = count($section);
		// return section
		return $section;
	}
	
	//----------------------------------------------------------------------
	// data accessors
	//----------------------------------------------------------------------
	
	public function getValue($name, $section = '', $parseConstants = true)
	{
		// return value (and parse constants)
		$section =& $this->parseVariableName($name, $section, false);
		return (!$section ? null :
		    ((!$parseConstants || !is_string($section[$name])) ? $section[$name] :
		    preg_replace('/\{([^}]+)\}/e', "constant('\\1')", $section[$name])));
	}
	
	public function setValue($name, $value, $section = '')
	{
		// add new entry
		$section =& $this->parseVariableName($name, $section, true);
		return $section[$name] = $value;
	}
	
	public function deleteValue($name, $section = '')
	{
		// delete entry
		if ($section =& $this->parseVariableName($name, $section, false))
			unset($section[$name]);
	}
	
	public function getSection($section = '')
	{
//[TODO] parse all constants?
		// return the section
		return $this->data[$section];
	}
	
	public function setSection($value, $section = '')
	{
		// set the section
		return $this->data[$section] = $value;
	}
	
	public function deleteSection($section = '')
	{
		// delete the section
		unset($this->data[$section]);
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