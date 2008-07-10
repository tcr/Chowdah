<?php

/**
 * HTTP Resource Base Class
 * @package chowdah.resources
 */

abstract class HTTPResourceBase implements IHTTPResource
{
	// supported default methods
	private $methods = array('CONNECT', 'DELETE', 'GET', 'HEAD', 'OPTIONS', 'POST', 'PUT', 'TRACE', 'TRACK');

	public function handle(HTTPRequest $request)
	{
		// handle HTTP request if method is allowed
		if (in_array($request->getMethod(), $this->getAllowedMethods()))
		{
			// analyze the method
			$class = new ReflectionClass(get_class($this));
			$method = $class->getMethod($request->getMethod());
			
			// get argument list
			$args = array($request);
			// only parse query/content if defined
			if ($method->getNumberOfParameters() >= 2)
			{
				// pass query data
				$args[1] = $request->getParsedQueryData();
			}
			if ($method->getNumberOfParameters() >= 3)
			{
				// pass parsed content
				$args[2] = $request->getParsedContent();
				// type checking
				$parameters = $method->getParameters();
				if (($type = $parameters[2]->getClass()) && (!is_object($args[2]) || !$type->isInstance($args[2])))
					throw new HTTPStatusException(HTTPStatus::BAD_REQUEST, null, 'The submitted content body of type ' . $request->getContentType()->serialize() . ' is unsupported.');
			}
			
			// call handler
			return $method->invokeArgs($this, $args);
		}
	
		// method not allowed
		return false;
	}
	
	public function getAllowedMethods()
	{
		// get available methods
		return array_intersect($this->methods, get_class_methods($this));
	}
	
	// default OPTIONS handler
	public function OPTIONS(HTTPRequest $request)
	{
		// create the response
		$response = new HTTPResponse();
		$response->setHeader('Allow', implode(', ', $this->getAllowedMethods()));
		return $response;
	}
}

?>