<?php

/**
 * Chowdah Functions
 * @package chowdah
 */

class Chowdah {
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
	
	static public function handle(HTTPRequest $request, IHTTPResource $root, $rootPath = null) {
		// get root andrequest path
		$rootPath = ($rootPath ? @realpath($rootPath) : @realpath($_SERVER['DOCUMENT_ROOT']));
		$requestPath = @realpath($_SERVER['DOCUMENT_ROOT']) . $request->getURL()->path;
		// validate requested path
		if (substr($requestPath, 0, strlen($rootPath)) != $rootPath)
			throw new Exception('Requested path does not match root path.');

		// get the requested path
		$path = array_map('urldecode', array_filter(explode('/', substr($requestPath, strlen($rootPath))), 'strlen'));
		// descend the tree
		$resource = $root;
		foreach ($path as $file)
			if (!($resource instanceof ICollection) || !($resource = $resource->getChild($file)))
				throw new HTTPStatusException(404);

		// check that a collection wasn't requested as a document
		if (substr($request->getURL()->path, -1) != '/' && $resource instanceof ICollection)
			throw new HTTPStatusException(301, 'Moved Permanently',
			    null, array('Location' => $request->getURL() . '/'));
		// or a document requested as a collection
		if (strlen($request->getURL()->path) > 1 && substr($request->getURL()->path, -1) == '/'
		    && !($resource instanceof ICollection))
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

	static public function handleCurrentRequest(IHTTPResource $root, $rootPath = null) {
		return Chowdah::handle(HTTPRequest::getCurrent(), $root, $rootPath);
	}
	
	//----------------------------------------------------------------------
	// logging
	//----------------------------------------------------------------------

	static public function log() {
		// check if a log file was requested
		if (!strlen($file = Chowdah::getConfigSetting('request_log')))
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
	
	static public function getConfigFile() {
		// load (cached) config file
		static $configFile = null;
		if (!$configFile)
			$configFile = new INIFile(Chowdah::getRootPath() . '/' . Chowdah::CONFIG_FILE);
		return $configFile;
	}
	
	static public function getConfigSetting($name) {
		// shorthand configuration checking
		return Chowdah::getConfigFile()->getValue($name);
	}
}

//------------------------------------------------------------------------------
// class importing
//------------------------------------------------------------------------------

// load importing functions
require_once 'import.php';

// import classes
import(Chowdah::getRootPath() . '/file');
import(Chowdah::getRootPath() . '/http');
import(Chowdah::getRootPath() . '/utils');
import(Chowdah::getRootPath() . '/resources');

?>