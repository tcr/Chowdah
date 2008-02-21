<?php 

class StripResource extends MacheResourceBase {
	//----------------------------------------------------------------------
	// construction
	//----------------------------------------------------------------------
	
	protected $strip;

	function __construct(Strip $strip) {
		// save strip reference
		$this->strip = $strip;
	}
	
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	protected $methods = array('GET', 'OPTIONS', 'POST', 'DELETE');
	
	public function GET(HTTPRequest $request) {
		// create an XML respresentation of this object
		$doc = new SimpleXMLElement('<strip />');
		$doc->addChild('id', $this->strip->id);
		$doc->addChild('title', $this->strip->title);
		$doc->addChild('content', $this->strip->content);
		$doc->addChild('tags');
		foreach ($this->strip->tags as $tag)
			$doc->tags->addChild('tag', $tag);
		// add query data
		foreach ($request->getParsedQueryData() as $key => $value)
			$doc[$key] = (string) $value;
		
		// create and send the response
		return $this->formatResponse($request, new HTTPResponse(), $doc);
	}
	
	public function POST(HTTPRequest $request) {
		// redirect other methods
		if ($request->parsedContent['method'] == 'DELETE')
			return $this->DELETE($request);
	
		// update strip
		$data = $request->getParsedContent();
		$this->strip->update($data['title'], $data['content'], preg_split('/\s+/', $data['tags'], -1, PREG_SPLIT_NO_EMPTY));
		
		// show updated page
		return $this->GET($request);
	}
	
	public function DELETE(HTTPRequest $request) {
		// delete this resource
		$this->strip->delete();
		
		// get location of parent resource
		$url = clone $request->getURL();
		$url->setComponents(array('query' => null, 'path' => dirname($url->path)));
		// redirect to strips list
		$response = new HTTPResponse();
		$response->setLocation($url);
		return $response;
	}
}

?>