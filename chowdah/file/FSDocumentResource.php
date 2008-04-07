<?php 

/**
 * Filesystem Document Resource
 * @package File
 */

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
#[TODO] move this to HTTPMessage, both in setContentAsDocument and setModificationTime()
		$response->setHeader('Last-Modified', date(DATE_RFC2822, $this->file->getModificationTime()));
		return $response;
	}
}

?>