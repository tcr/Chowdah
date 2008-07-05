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
#[TODO] should these be on by default?
		if (Chowdah::getConfigSetting('catch_errors'))
			set_error_handler(array('Chowdah', 'errorHandler'), error_reporting());
		if (Chowdah::getConfigSetting('catch_exceptions'))
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
		// display the error message
		$exception->getHTTPResponse()->send();
#[TODO] log exception?
	}
	
	static public function errorHandler($errno, $errstr, $errfile, $errline)
	{
		// if we're reporting errors, display a 500 Internal Server Error
		if (error_reporting())
			$exception = new HTTPStatusException(500, 'Internal Server Error', $errstr);
#[TODO] log error?
	}

	//----------------------------------------------------------------------
	// request handler
	//----------------------------------------------------------------------
	
	static public function handle(HTTPRequest $request, IHTTPResource $root)
	{
		// get the requested path
		$path = array_map('urldecode', array_filter(explode('/', $request->getURL()->path), 'strlen'));
		// get the root application path
		$rootPath = Chowdah::getApplicationPath();
		$tmpPath = @realpath($_SERVER['DOCUMENT_ROOT']);
		while ($tmpPath != $rootPath && count($path))
			$tmpPath = @realpath($tmpPath . '/' . array_shift($path));
		if ($tmpPath != $rootPath)
			throw new Exception('The path of the Chowdah application could not be resolved.');
			
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

	static public function handleCurrentRequest(IHTTPResource $root)
	{
		// shorthand to handle current HTTP request using Chowdah
		return Chowdah::handle(Chowdah::getCurrentRequest(), $root);
	}
	
	static public function getCurrentRequest()
	{
		// get the current HTTP request
		$request = HTTPRequest::getCurrent();
		
#[TODO] better specify this format
		// fix browser Accept: strings
		if ($agent = $request->getUserAgentInfo()) {
			// get override
			$accept = Chowdah::getConfigSetting('accept');
			if ($override = $accept[$agent->browser][$agent->majorver])
				$request->setHeader('Accept', $override);
		}
		
		// html form compatibility
		if (Chowdah::getConfigSetting('html_form_compat'))
		{
			// set method
			if (is_string($request->parsedContent['request_method']))
				$request->setMethod($request->parsedContent['request_method']);
			// set content
			if ($request->parsedContent['request_content'] instanceof IDocument)
				$request->setContentAsDocument($request->parsedContent['request_content']);
		}
		
		// HTTP Authorization header workaround
		if (!function_exists('getallheaders') && !$_SERVER['HTTP_AUTHORIZATION']
		    && Chowdah::getConfigSetting('auth_header_key'))
			$request->setHeader('Authorization', $_SERVER[Chowdah::getConfigSetting('auth_header_key')]);
		
		// return the modified request
		return $request;
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
	const CONFIG_FILE = 'config.ini';
	
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