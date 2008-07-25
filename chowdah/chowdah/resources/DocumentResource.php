<?php 

/**
 * Document Resource
 * @package chowdah.resources
 */

class DocumentResource extends HTTPResourceBase
{
	//----------------------------------------------------------------------
	// construction
	//----------------------------------------------------------------------
	
	protected $file;
	
	function __construct(IDocument $file)
	{
		// save the internal object
		$this->file = $file;
	}
	
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------

	public function GET(HTTPRequest $request)
	{
		// display the file
		$response = new HTTPResponse();
		$response->setContentAsDocument($this->file);
		return $response;
	}
}

?>