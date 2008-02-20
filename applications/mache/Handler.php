<?php

import('resources');
import('textile');

class Handler {
	public static function call(HTTPRequest $request) {
		// call the request handler
		Chowdah::handle($request, new RootResource())->send();
	}
}

?>