<?php

//------------------------------------------------------------------------------
// chowdah filesystem document resource
//------------------------------------------------------------------------------

class ChowdahFSDocumentResource implements HTTPResource, Document {
	// internal document object
	protected $file;
	
	function __construct(ChowdahFSDocument $file) {
		// save the internal object
		$this->file = $file;
	}

	//----------------------------------------------------------------------
	// Document functions
	//----------------------------------------------------------------------
	
	public function getContent() {
		return $this->file->getContent();
	}
		
	public function getContentType() {
		return $this->file->getContentType();
	}	
	
	public function getSize() {
		return $this->file->getSize();
	}
	
	//----------------------------------------------------------------------
	// Chowdah resource functions
	//----------------------------------------------------------------------
	
	public function handle(HTTPRequest $request) {
		// create the response
		$response = new HTTPResponse();
		
		// handle the request
		switch ($request->getMethod()) {
		    case 'GET':
			// display the file
			$response->setContentAsDocument($this);
			break;
		
		    default:
			return false;
		}
		
		// send response
		return $response;
	}

	public function getAllowedMethods() {
		return array('GET');
	}
}

?>