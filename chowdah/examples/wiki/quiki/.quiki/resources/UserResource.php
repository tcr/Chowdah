<?php 

class UserResource extends QuikiResourceBase
{
	//----------------------------------------------------------------------
	// construction
	//----------------------------------------------------------------------
	
	protected $user;

	function __construct(User $user) {
		// save user reference
		$this->user = $user;
	}
	
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	protected $methods = array('GET', 'OPTIONS');
	
	public function GET(HTTPRequest $request) {
		// create an XML respresentation of this object
		$doc = new SimpleXMLElement('<user />');
		$doc->name = $this->user->name;
		
		// create and send the response
		return $this->formatResponse($request, new HTTPResponse(), $doc);
	}
}

?>