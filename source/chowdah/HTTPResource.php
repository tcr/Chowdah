<?php

interface HTTPResource {
	// handle an HTTPRequest
	public function handle(HTTPRequest $request);
	
	// return an array of allowed HTTP methods
	public function getAllowedMethods();
}

?>