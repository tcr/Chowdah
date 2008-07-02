<?php

/**
 * HTTP Resource Base Class
 * @package chowdah.resources
 */

abstract class HTTPResourceBase implements IHTTPResource {
	// allowed HTTP methods
	protected $methods = array('OPTIONS');

	public function handle(HTTPRequest $request) {
		// handle HTTP request if method is allowed
		if (in_array($request->getMethod(), $this->methods))
			return $this->{$request->getMethod()}($request);
			
		// method not allowed
		return false;
	}
	
	// allowed methods
	public function getAllowedMethods() {
		return $this->methods;
	}
	
	// default OPTIONS class
	public function OPTIONS(HTTPRequest $request) {
		// create the response
		$response = new HTTPResponse();
		$response->setHeader('Allow', implode($this->methods));
		return $response;
	}
}

?>