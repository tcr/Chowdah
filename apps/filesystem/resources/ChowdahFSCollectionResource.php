<?php

//------------------------------------------------------------------------------
// chowdah filesystem collection resource
//------------------------------------------------------------------------------

class ChowdahFSCollectionResource extends FSCollectionResource {
	function __construct(ChowdahFSCollection $file) {
		// save the internal object
		parent::__construct($file, $this->file->getMetadata('allow-directory-list']));
	}
	
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------

	public function GET(HTTPRequest $request) {
		// check if there is a directory index
		if (isset($this->file->metadata['index']) &&
		    ($child = $this->getChild($this->file->getMetadata('index'))))
			return $child->GET($request);

		// call request handler
		return parent::GET($request);
	}

	//----------------------------------------------------------------------
	// collection
	//----------------------------------------------------------------------

	public function getChild($filename) {
		// get child file
		$child = parent::getChild($filename);
		// else return the child object
		return !$child ? false :
		    $child instanceof Collection ?
		        new ChowdahFSCollectionResource($child) :
		        new ChowdahFSDocumentResource($child);
	}
}

?>
