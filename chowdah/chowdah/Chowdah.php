<?php

/**
 * Chowdah Functions
 * @package chowdah
 */

class Chowdah
{
	//----------------------------------------------------------------------
	// initialization functions
	//----------------------------------------------------------------------

	static public function init()
	{
		// load configuration data
		Chowdah::loadConfigSettings();
			
		// exception/error handling
#[TODO] what should be the default error handling
		if (Chowdah::getConfigSetting('catch_errors') !== false)
			set_error_handler(array('Chowdah', 'errorHandler'), error_reporting());
		if (Chowdah::getConfigSetting('catch_exceptions') !== false)
			set_exception_handler(array('Chowdah', 'exceptionHandler'));
		
		// stat logging
		Chowdah::logStats();
		register_shutdown_function(array('Chowdah', 'logStats'));
	}
	
	static public function getLibraryPath()
	{
		// get root path
		return dirname(__FILE__);
	}
	
	static public function getApplicationPath()
	{
		// return the root of the application (parent of the request handler)
		if (!($root = Chowdah::getConfigSetting('application_root')))
			$root = '..';
		return @realpath($_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['SCRIPT_NAME']) . '/' . $root);
	}

	//----------------------------------------------------------------------
	// error handling
	//----------------------------------------------------------------------
	
	static public function exceptionHandler($exception)
	{
		// if the exception is generic, throw a 500 Internal Server Error
		if (!($exception instanceof HTTPStatusException))
			$exception = new HTTPStatusException(500, 'Internal Server Error', $exception->getMessage());
		
		// display the error message and stop execution
		$exception->getHTTPResponse()->send();
		die();
#[TODO] log exception?
	}
	
	static public function errorHandler($errno, $errstr, $errfile, $errline)
	{
		// display errors as a 500 Internal Server Error
		if (error_reporting())
			throw new HTTPStatusException(500, 'Internal Server Error', strip_tags(html_entity_decode($errstr)));
#[TODO] log error?
	}

	//----------------------------------------------------------------------
	// request handler
	//----------------------------------------------------------------------
	
	static public function handle(HTTPRequest $request, IHTTPResource $root)
	{	
		// get the requested path
		$path = array_map('urldecode', array_filter(explode('/', $request->getURL()->path), 'strlen'));
		$applicationPath = Chowdah::getRelativeApplicationPath($request);

		// descend the tree
		$resource = $root;
		foreach (array_slice($path, count(explode('/', $applicationPath)) - 1) as $file)
			if (!($resource instanceof ICollection) || !($resource = $resource->getChild($file)))
				throw new HTTPStatusException(HTTPStatus::NOT_FOUND);

		// check that a collection wasn't requested as a document
		if (substr($request->getURL()->path, -1) != '/' && $resource instanceof ICollection)
			throw new HTTPStatusException(HTTPStatus::MOVED_PERMANENTLY, null,
			    null, array('Location' => $request->getURL() . '/'));
		// or a document requested as a collection
		if (strlen($request->getURL()->path) > 1 && substr($request->getURL()->path, -1) == '/'
		    && !($resource instanceof ICollection))
			throw new HTTPStatusException(HTTPStatus::NOT_FOUND);

		// check that this method is supported
		$methods = $resource->getAllowedMethods();
		if (!in_array($request->getMethod(), $methods) && !in_array('*', $methods))
			throw new HTTPStatusException(HTTPStatus::METHOD_NOT_ALLOWED, null,
			    'The method "' . $request->getMethod() . '" is not allowed on this resource.',
			    array('Allow' => implode(', ', $methods)));
		// call the resource handler with the current request
		$response = $resource->handle($request);
		if (!($response instanceof HTTPResponse))
			throw new HTTPStatusException(HTTPStatus::NO_CONTENT);
			    
		// return the response
		return $response;
	}

	static public function handleCurrentRequest(IHTTPResource $root)
	{
		// shorthand to handle current HTTP request using Chowdah
		return Chowdah::handle(HTTPRequest::getCurrent(), $root);
	}
	
	public static function getRelativeApplicationPath(HTTPRequest $request) {
		// get the requested path
		$path = array_map('urldecode', array_filter(explode('/', $request->getURL()->path), 'strlen'));
		// get the root application path
		$applicationPath = '';
		$rootPath = Chowdah::getApplicationPath();
		while (@realpath($_SERVER['DOCUMENT_ROOT'] . $applicationPath) != $rootPath && count($path))
			$applicationPath .= '/' . array_shift($path);
		if (@realpath($_SERVER['DOCUMENT_ROOT'] . $applicationPath) != $rootPath)
			throw new Exception('The path of the Chowdah application could not be resolved.');
		return $applicationPath;
	}
	
	//----------------------------------------------------------------------
	// logging
	//----------------------------------------------------------------------

	static public function log()
	{
		// check if a log file was requested
		if (!strlen($file = Chowdah::getConfigSetting('request_log')))
			return false;
			
		// log the supplied arguments
		foreach (func_get_args() as $arg)
			file_put_contents(Chowdah::getLibraryPath() . '/' . $file, (string) $arg . "\n", FILE_APPEND);
	}

	static public function logStats()
	{
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
	const CONFIG_FILE = 'chowdah.ini';
	
	protected static $configSettings = array();
	
	static public function loadConfigSettings()
	{
		if (!is_file(Chowdah::getLibraryPath() . '/' . Chowdah::CONFIG_FILE))
			return false;
	
		// load settings
		$config = new INIFile(Chowdah::getLibraryPath() . '/' . Chowdah::CONFIG_FILE);
		Chowdah::$configSettings = (array) $config->getSection();
	}
	
#[TODO] what about sections?
	static public function getConfigSetting($name)
	{
		return Chowdah::$configSettings[$name];
	}
	
	static public function setConfigSetting($name, $value)
	{
		return (Chowdah::$configSettings[$name] = $value);
	}
	
	static public function deleteConfigSetting($name)
	{
		unset(Chowdah::$configSettings[$name]);
	}
}

//------------------------------------------------------------------------------
// class importing
//------------------------------------------------------------------------------

// load importing functions
require_once 'import.php';

// import classes
import(Chowdah::getLibraryPath() . '/file');
import(Chowdah::getLibraryPath() . '/http');
import(Chowdah::getLibraryPath() . '/utils');
import(Chowdah::getLibraryPath() . '/resources');

?>