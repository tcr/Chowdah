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
			throw new Exception('No Application specified.');
		Chowdah::loadApplication(Chowdah::getArgument('application'));

#[TODO] do this part. separate applications (AGAIN), then autoload for an application structure (resources/ and such) and load.
# also, note, filesystem should NOT be default. this should, because filesystem is really proprietary and weird. and insecure.
		
		// call the request handler
#[TODO] pass request to rootresource?
		Chowdah::handle($request, new RootResource())->send();
	}
}

?>