<?php 

class PageNotFoundResource extends QuikiResourceBase
{
	//----------------------------------------------------------------------
	// construction
	//----------------------------------------------------------------------
	
	protected $title;

	function __construct($title) {
		// save page title
		$this->title = $title;
	}
	
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	protected $methods = array('GET', 'OPTIONS', 'PUT');
	
	public function GET(HTTPRequest $request) {
		// create an XML respresentation of this object
		$doc = new SimpleXMLElement('<page />');
		$doc->addChild('title', $this->title);
		// add query data
		foreach ($request->getParsedQueryData() as $key => $value)
			$doc[$key] = (string) $value;
		
		// create and send the response
		return $this->formatResponse($request, new HTTPResponse(), $doc);
	}
	
	public function PUT(HTTPRequest $request) {
		// create this page
		$data = $request->getParsedContent();
		$page = Page::create($this->title, $data['content'], preg_split('/\s+/', $data['tags'], -1, PREG_SPLIT_NO_EMPTY));
		
		// show updated page
		$resource = new PageResource($page);
		$response = $resource->GET($request);
		$response->setStatus(HTTPResponse::STATUS_CREATED);
		return $response;
	}
}

?>