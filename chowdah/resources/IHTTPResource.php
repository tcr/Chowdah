<?php

/**
 * HTTP Resource Interface
 * @package chowdah.resources
 */

interface IHTTPResource {
	// handle an HTTPRequest
	public function handle(HTTPRequest $request);
	
	// return an array of allowed HTTP methods
	public function getAllowedMethods();
}

?>