<?php

//##############################################################################
// Chowdah | REST Framework
//##############################################################################
// written by Tim Ryan (tim-ryan.com)
// released under the MIT/X License
// we are not hackers!
//##############################################################################

require_once "file.php";
require_once "http.php";

//==============================================================================
// Chowdah class
//==============================================================================

class Chowdah {
	// class constants
	const LOG_FILE = 'chowdah-log.txt';
	const APPLICATIONS_INDEX = 'applications.xml';
	
	// applications cache
	static $applications = array();

	//----------------------------------------------------------------------
	// initialization functions
	//----------------------------------------------------------------------

	static public function init() {
		// initialize applications
		ChowdahApplication::init();
		
		// stat logging
		Chowdah::logStats();
		register_shutdown_function(array('Chowdah', 'logStats'));
	}

	//----------------------------------------------------------------------
	// request handler
	//----------------------------------------------------------------------
	
	#[TODO] OPTIONS header? allow?
	
	static public function handle(HTTPRequest $request, ChowdahResource $root) {
		try {
			// get the requested path
			$path = array_filter(explode('/', $request->getURIComponents()->path), 'strlen');
			// descend the tree
			$resource = $root;
			foreach ($path as $file)
				if (!($resource instanceof Collection) || !($resource = $resource->getChild($file)))
					throw new HTTPStatusException(404);
			
			// check that a collection wasn't requested as a document
			if (substr($request->getURIComponents()->path, -1) != '/' && $resource instanceof Collection)
				throw new HTTPStatusException(301, 'Moved Permanently',
				    null, array('Location' => $request->getURI() . '/'));
			// or a document requested as a collection
			if (substr($request->getURIComponents()->path, -1) == '/' && $resource instanceof Document)
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
		} catch (HTTPStatusException $exception) {
			// submit the error message
			return $exception->getHTTPResponse();
		} catch (Exception $exception) {
			// submit a 500 internal server error
			$exception = new HTTPStatusException(500, 'Internal Server Error', $exception->getMessage());
			return $exception->getHTTPResponse();
		}
	}

	//----------------------------------------------------------------------
	// resource creation
	//----------------------------------------------------------------------
	
	static public function createResource($type, $args = array()) {
		// get the application and type
		list ($app, $type) = explode(':', $type, 2);
		if (!($app = ChowdahApplication::load($app)))
			return false;
		// create the resource
		return $app->createResource($type, $args);
	}
	
	//----------------------------------------------------------------------
	// logging
	//----------------------------------------------------------------------

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

//=============================================================================
// Chowdah application class
//=============================================================================

class ChowdahApplication {
	// paths
	protected $path;
	protected $configPath;
	
	// application properties
	protected $id;
	protected $title;
	protected $description;
	protected $includes = array();
	protected $types = array();
	
	function __construct($configPath) {
		// save the path
		$this->configPath = @realpath($configPath);
		$this->path = dirname($this->configPath);
		
		// load and parse the application file
		$doc = @simplexml_load_file($this->configPath);
		$this->id = (string) $doc['id'];
		$this->title = (string) $doc->title;
		$this->description = (string) $doc->description;
		// resource types
		foreach ($doc->{'resource-types'}->{'resource-type'} as $type)
			$this->types[(string) $type] = (object) array(
				'class' => (string) $type['class']
			    );

		// add includes
		foreach ($doc->includes->children() as $include) {
			switch ($include->getName()) {
			    case 'file':
				require_once $this->path . '/' . ((string) $include);
				break;

			    case 'application':
				if (!ChowdahApplication::load((string) $include))
					throw new Exception('Could not include application dependency "' . ((string) $include) . '".');
				break;
			}
		}
	}
	
	//---------------------------------------------------------------------
	// resource creation
	//---------------------------------------------------------------------
	
	public function createResource($type, $args = array()) {
		// validate the type
		if (!preg_match('/^[0-9a-z_\-\.]+$/', $type))
			return false;
		// get the resource type object
		$type = $this->types[$type];
		if (!class_exists($class = $type->class))
			return false;
		
		// return a new resource object
		$class = new ReflectionClass($class);
		return call_user_func_array(array($class, 'newInstance'),
		    $class->getConstructor() ? $args : array());
	}
	
	//---------------------------------------------------------------------
	// applications loading
	//---------------------------------------------------------------------

	// applications cache
	static $cache = array();
	
	static public function init() {
		// parse applications file
		$apps = @simplexml_load_file(Chowdah::APPLICATIONS_INDEX);
		foreach ($apps->application as $app)
			if (preg_match('/^[0-9a-z_\-]+$/i', (string) $app['id']) && is_file((string) $app))
				ChowdahApplication::$cache[(string) $app['id']] = (string) $app;
	}
	
	static public function load($id) {
		// check if the application exists in the cache
		if (!isset(ChowdahApplication::$cache[$id]))
			return false;
		else if (ChowdahApplication::$cache[$id] instanceof ChowdahApplication)
			return ChowdahApplication::$cache[$id];
	
		try {
			// get the application address and nullify it (to prevent recursive dependencies)
			$appPath = ChowdahApplication::$cache[$id];
			unset(ChowdahApplication::$cache[$id]);
			// attempt to load and cache the requested application
			$app = new ChowdahApplication($appPath);
			return ChowdahApplication::$cache[$id] = $app;
		} catch (Exception $e) {
			// application could not be loaded
			return false;
		}
	}
}

//=============================================================================
// Chowdah resource interface
//=============================================================================

interface ChowdahResource {
	// handle an HTTPRequest
	public function handle(HTTPRequest $request);
	
	// return an array of allowed HTTP methods
	public function getAllowedMethods();
}

?>