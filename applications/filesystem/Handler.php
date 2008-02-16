<?php

//##############################################################################
// Chowdah | Filesystem Request Handler
//##############################################################################
// written by Tim Ryan (tim-ryan.com)
// released under the MIT/X License
// we are not hackers!
//##############################################################################

#[TODO] have independent FSDocumentResource class?
#[TODO] creating resource from DOCUMENT_ROOT could throw exception, so...

class Handler {
	public static function call(HTTPRequest $request) {
		// get the document root
		$root = new ChowdahFSCollection($_SERVER['DOCUMENT_ROOT']);
		$filename = $root->getFilename();
		$root = new ChowdahFSCollectionResource($root->getParent());
		$root = $root->getChild($filename);
		
		// call the request handler
		Chowdah::handle($request, $root)->send();
	}
}

?>