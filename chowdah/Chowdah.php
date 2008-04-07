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
		// setup import loader
		spl_autoload_register('import_autoload');

		// import classes
		import(Chowdah::getRootPath());
		import(Chowdah::getRootPath() . '/file');
		import(Chowdah::getRootPath() . '/http');

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
		if (!strlen($file = Chowdah::getConfigValue('request_log')))
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
	const CONFIG_FILE = 'config.ini';
	
	static public function loadConfig() {
		return parse_ini_file(Chowdah::getRootPath() . '/' . Chowdah::CONFIG_FILE);
	}
	
	static public function saveConfig($config) {
		// write config file
		$ini = '';
		foreach ($config as $name => $value)
			$ini .= $name . ' = "' . addslashes($value) . '"' . "\n";
		return file_put_contents(Chowdah::getRootPath() . '/' . Chowdah::CONFIG_FILE, $ini);
	}
	
	static public function getConfigValue($name) {
		$config = Chowdah::loadConfig();
		return $config[$name];
	}
	
	static public function setConfigValue($name, $value) {
		// add new entry
		$config = Chowdah::loadConfig();
		$config[$name] = $value;

		// write document
		return Chowdah::saveConfig($config);
	}
	
	static public function deleteConfigValue($name) {
		// delete entry
		$config = Chowdah::loadConfig();
		unset($config[$name]);
			
		// write document
		return Chowdah::saveConfig($config);
	}
}

//------------------------------------------------------------------------------
// import files
//------------------------------------------------------------------------------

$importFolders = array();

function import_autoload($class) {
	global $importFolders;
	foreach ($importFolders as $folder) {
		if (is_file($folder . '/' . $class . '.php'))
			include_once $folder . '/' . $class . '.php';
	}
}

function import($folder) {
	// add folder to __autoload list
	global $importFolders;
	$importFolders[] = @realpath($folder);
}

?>