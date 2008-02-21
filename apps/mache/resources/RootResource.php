<?php 

class RootResource extends MacheResourceBase implements Collection {
	//----------------------------------------------------------------------
	// construction
	//----------------------------------------------------------------------
	
	protected $strip;

	function __construct() {
		// get main strip
		$this->strip = new Strip('');
	}
	
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	protected $methods = array('GET', 'OPTIONS', 'POST');
	
	public function GET(HTTPRequest $request) {
		// create an XML respresentation of this object
		$doc = new SimpleXMLElement('<root />');
		$doc->addChild('title', $this->strip->title);
		$doc->addChild('content', $this->strip->content);
		// add query data
		foreach ($request->getParsedQueryData() as $key => $value)
			$doc[$key] = (string) $value;
		
		// create and send the response
		return $this->formatResponse($request, new HTTPResponse(), $doc);
	}
	
	public function POST(HTTPRequest $request) {	
		// update strip
		$data = $request->getParsedContent();
		$this->strip->update($data['title'], $data['content'], array());
		
		// show updated page
		return $this->GET($request);
	}

	//----------------------------------------------------------------------
	// collection
	//----------------------------------------------------------------------

	public function getChild($filename) {
		// get the child resource
		switch ($filename) {
		    case 'strips': return new StripsResource();
		    case 'styles': return new FSCollectionResource(new FSCollection('../styles'));
		    default: return false;
		}
	}
}

?>