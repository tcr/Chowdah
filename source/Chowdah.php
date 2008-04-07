<?php

/**
 * Chowdah Functions
 * @package Chowdah
 */

abstract class Chowdah {
	//----------------------------------------------------------------------
	// initialization functions
	//----------------------------------------------------------------------

	static public function init() {
		// exception/error handling
#[TODO]		set_error_handler(array('Chowdah', 'errorHandler'), error_reporting());
		set_exception_handler(array('Chowdah', 'exceptionHandler'));
		
		// stat logging
		Chowdah::logStats();
		register_shutdown_function(array('Chowdah', 'logStats'));
	}
	
	static public function getRootPath() {
		// get root path
		return dirname(__FILE__);
	}
	
	static public function getArgument($name) {
		// load server args
		parse_str($_SERVER['argv'][0], $args);
		return $args[$name];
	}

	//----------------------------------------------------------------------
	// error handling
	//----------------------------------------------------------------------
	
	static public function exceptionHandler($exception) {
		// if the exception is generic, throw a 500 Internal Server Error
		if (!($exception instanceof HTTPStatusException))
			$exception = new HTTPStatusException(500, 'Internal Server Error', $exception->getMessage());
		// display the error message
		$exception->getHTTPResponse()->send();
	}
	
	static public function errorHandler($errno, $errstr, $errfile, $errline) {
		// convert errors to exceptions (for presentation sake)
		if (error_reporting())
			throw new Exception($errstr, $errno);
	}

	//----------------------------------------------------------------------
	// request handler
	//----------------------------------------------------------------------
	
	static public function handle(HTTPRequest $request, HTTPResource $root) {
		// get the requested path
		$path = array_filter(explode('/', $request->getURL()->path), 'strlen');
		// descend the tree
		$resource = $root;
		foreach ($path as $file)
			if (!($resource instanceof Collection) || !($resource = $resource->getChild($file)))
				throw new HTTPStatusException(404);
		
		// check that a collection wasn't requested as a document
		if (substr($request->getURL()->path, -1) != '/' && $resource instanceof Collection)
			throw new HTTPStatusException(301, 'Moved Permanently',
			    null, array('Location' => $request->getURL() . '/'));
		// or a document requested as a collection
		if (substr($request->getURL()->path, -1) == '/' && !($resource instanceof Collection))
			throw new HTTPStatusException(404);
		
		// call the resource handler with the current request
		$response = $resource->handle($request);
		// if no response was returned, method is not allowed
		if (!($response instanceof HTTPResponse))
			throw new HTTPStatusException(405, 'Method Not Allowed',
			    'The method "' . $request->getMethod() . '" is not allowed on this resource.',
			    array('Allow' => implode(', ', $resource->getAllowedMethods())));
		// return the response
		return $response;
	}

	//----------------------------------------------------------------------
	// applications
	//----------------------------------------------------------------------
	
#[TODO] make sure when loading that the app acutally exists...

	static public function loadApplication($path) {
		// load Chowdah path (to make relative paths work)
		chdir(Chowdah::getRootPath());
		// change working directory to application path
		chdir($path);
		// import classes from this directory
		import('.');
		return true;
	}
	
	//----------------------------------------------------------------------
	// logging
	//----------------------------------------------------------------------

	static public function log() {
		// check if a log file was requested
		if (!strlen($file = Chowdah::getConfigValue('log')))
			return false;
			
		// log the supplied arguments
		foreach (func_get_args() as $arg)
			file_put_contents(Chowdah::getRootPath() . '/' . $file, (string) $arg . "\n", FILE_APPEND);
	}

	static public function logStats() {
		static $start_time = null;
	
		// get the stat data
		$time_usage = time() + microtime() - $start_time;
		$memory_usage = ceil(memory_get_usage() / 1024);
		
		// log data
		if ($start_time !== null)
			Chowdah::log(sprintf("%s\t%.14fs\t%s\t%s\t%s KB", date('F j, Y, g:i a'),
			    $time_usage, $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $memory_usage));
	   
		// save stat data
		$start_time = time() + microtime();
	}
	
	//----------------------------------------------------------------------
	// configuration
	//----------------------------------------------------------------------

	// configuration location
	const CONFIG_FILE = 'config.xml';
	
	static public function loadConfig() {
		return simplexml_load_file(Chowdah::getRootPath() . '/' . Chowdah::CONFIG_FILE);
	}
	
	static public function saveConfig(SimpleXMLElement $config) {
		// write config file
		$doc = new DOMDocument();
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;
		$doc->loadXML($config->asXML());
		return (bool) $doc->save(Chowdah::getRootPath() . '/' . Chowdah::CONFIG_FILE);
	}
	
	static public function getConfigValue($name) {
		$entry = Chowdah::loadConfig()->xpath('entry[@name="' . $name . '"]');
		return (string) $entry[0];
	}
	
	static public function setConfigValue($name, $value) {
		// delete old entries
		Chowdah::deleteConfigValue($name);
		// add new entry
		$config = Chowdah::loadConfig();
		$entry = $config->addChild('entry', $value);
		$entry['name'] = $name;
		
		// write document
		return Chowdah::saveConfig($config);
	}
	
	static public function deleteConfigValue($name) {
		// delete entries
		$config = Chowdah::loadConfig();
		$entry = $config->xpath('entry[@name="' . $name . '"]');
		foreach ($entry as $item)
			unset($item[0]);
			
		// write document
		return Chowdah::saveConfig($config);
	}
}

?>