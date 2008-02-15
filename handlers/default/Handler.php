<?php

//##############################################################################
// Chowdah | Filesystem Request Handler
//##############################################################################
// written by Tim Ryan (tim-ryan.com)
// released under the MIT/X License
// we are not hackers!
//##############################################################################

class Handler {
	public static function call(HTTPRequest $request) {
		// load requested Application
		if (!Chowdah::getArgument('application'))
			throw new Exception('No Chowdah application was specified.');
		Chowdah::loadApplication(Chowdah::getArgument('application'));
		
		// call the request handler
#[TODO] pass request to rootresource?
		Chowdah::handle($request, Chowdah::createResource('RootResource'))->send();
	}
}

?>