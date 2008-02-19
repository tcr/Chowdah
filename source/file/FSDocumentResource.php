<?php 

//------------------------------------------------------------------------------
// filesystem document resource
//------------------------------------------------------------------------------

class FSDocumentResource extends HTTPResourceBase {
	//----------------------------------------------------------------------
	// construction
	//----------------------------------------------------------------------
	
	protected $file;
	
	function __construct(FSDocument $file) {
		// save the internal object
		$this->file = $file;
	}
	
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	protected $methods = array('GET', 'OPTIONS');
	
	public function GET(HTTPRequest $request) {
		// display the file
		$response = new HTTPResponse();
		$response->setContentAsDocument($this->file);
		return $response;
	}
}

?>