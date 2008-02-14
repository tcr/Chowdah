<?php

//##############################################################################
// Chowdah | REST Framework
//##############################################################################
// written by Tim Ryan (tim-ryan.com)
// released under the MIT/X License
// we are not hackers!
//##############################################################################

#[TODO] config control

//==============================================================================
// Chowdah class
//==============================================================================

class Chowdah {
#[TODO] eliminate these using config:
	// class constants
	const LOG_FILE = 'chowdah.log';
	const APPLICATIONS_INDEX = 'applications.xml';
	
	//----------------------------------------------------------------------
	// initialization functions
	//----------------------------------------------------------------------

	static public function init() {
		// add autoload function
#		spl_autoload_register(array('Chowdah', 'autoload'));
		
		// exception/error handling
#		set_error_handler(array('Chowdah', 'errorHandler'), error_reporting());
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
	
	static public function setCurrentApplication($id) {
		// load applications file
		$apps = simplexml_load_file(dirname(__FILE__) . '/' . Chowdah::APPLICATIONS_INDEX);
		// search for named application
		$app = $apps->xpath('/chowdah-applications/application[@id="' . $id . '"]');
		if (!count($app))
			return false;
		
		// change current working directory
		chdir(dirname(__FILE__) . '/' . $app[0]);
		return true;
	}
	
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
	
#	static public function autoload($class) {
#		require_once $class . '.php';
#	}
	
	//----------------------------------------------------------------------
	// logging
	//----------------------------------------------------------------------

#[TODO] use specified log file
	static public function log() {
		// log the supplied arguments
		foreach (func_get_args() as $arg)
			file_put_contents(dirname($_SERVER['SCRIPT_FILENAME']) . '/'
			    . Chowdah::LOG_FILE, (string) $arg . "\n", FILE_APPEND);
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
}

?>