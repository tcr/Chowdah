<?php

/**
 * HTTP Resource Base Class
 * @package chowdah.resources
 */

abstract class HTTPResourceBase implements IHTTPResource
{
	public function handle(HTTPRequest $request)
	{
		// handle HTTP request if method is allowed
		if (in_array($request->getMethod(), $this->getAllowedMethods()))
			return $this->{$request->getMethod()}($request);
			
		// method not allowed
		return false;
	}
	
	// HTTP methods cache
	private $methodsCache;
	
	// allowed HTTP methods
	public function getAllowedMethods()
	{
		// return cached methods array
		if (is_array($this->methodsCache))
			return $this->methodsCache;
		
		// get method list
		$this->methodsCache = array();
		$class = new ReflectionClass(get_class($this));
		foreach ($class->getMethods() as $method)
		{
			// screen methods
			if (!$method->isPublic() || $method->getNumberOfParameters() != 1 ||
			    ($method->getDeclaringClass() == new ReflectionClass('HTTPResourceBase')
			        && $method->getName() == 'handle'))
				continue;
			
			// screen parameters
			$params = $method->getParameters();
			if ($params[0]->getClass() == new ReflectionClass('HTTPRequest'))
				$this->methodsCache[] = $method->getName();
		}
		return $this->methodsCache;
	}
	
	// default OPTIONS handler
	public function OPTIONS(HTTPRequest $request)
	{
		// create the response
		$response = new HTTPResponse();
		$response->setHeader('Allow', implode($this->getAllowedMethods()));
		return $response;
	}
}

?>