<?php

//##############################################################################
// Chowdah | REST Framework
//##############################################################################
// written by Tim Ryan (tim-ryan.com)
// released under the MIT/X License
// we are not hackers!
//##############################################################################

//==============================================================================
// Chowdah class
//==============================================================================

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
	
	static public function loadApplication($id) {
		// search for named application
		$app = Chowdah::loadConfig()->apps->xpath('app[@id="' . addslashes($id) . '"]')
		if (!count($app))
			return false;

		// add autoload function
		spl_autoload_register(array('Chowdah', 'autoload'));
		
		// change current working directory
		chdir(dirname(__FILE__) . '/' . ((string) $app[0]));
		return true;
	}

#[TODO] legit?
	static public function createResource($className, $args = array()) {
		// check that the class is loaded
		if (!class_exists($className, false))
			require_once 'resources/' . $className . '.php';
			
		// create the new resource
		$reflectionClass = new ReflectionClass($className);
		return $reflectionClass->getConstructor() ?
		    $reflectionClass->newInstanceArgs($args) :
		    $reflectionClass->newInstance(); 
	}
	
	static public function autoload($class) {
		@include $class . '.php';
		@include 'resources/' . $class . '.php';
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
			file_put_contents(dirname($_SERVER['SCRIPT_FILENAME']) . '/'
			    . $file, (string) $arg . "\n", FILE_APPEND);
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
	
#[TODO] further config control

	// configuration location
	const CONFIG_FILE = 'config.xml';
	
	static public function loadConfig() {
		return simplexml_load_file(Chowdah::CONFIG_FILE);
	}
	
	static public function getConfigValue($item) {
		$entry = Chowdah::loadConfig()->config->xpath('entry[@name="' . $item . '"]');
		return (string) $entry[0];
	}
}

?>