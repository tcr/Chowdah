<?php

/**
 * HTTP Resource Interface
 * @package chowdah
 */

interface IHTTPResource {
	// handle an HTTPRequest
	public function handle(HTTPRequest $request);
	
	// return an array of allowed HTTP methods
	public function getAllowedMethods();
}

?>