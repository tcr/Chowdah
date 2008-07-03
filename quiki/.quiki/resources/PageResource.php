<?php 

class PageResource extends QuikiResourceBase
{
	//----------------------------------------------------------------------
	// construction
	//----------------------------------------------------------------------
	
	protected $page;

	function __construct(Page $page) {
		// save page reference
		$this->page = $page;
	}
	
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	protected $methods = array('OPTIONS', 'GET', 'PUT', 'DELETE');
	
	public function GET(HTTPRequest $request) {
		// create an XML respresentation of this object
		$doc = new SimpleXMLElement('<page />');
		$doc->addChild('title', $this->page->title);
		$doc->addChild('content', $this->page->content);
		$doc->addChild('tags');
		foreach ($this->page->tags as $tag)
			$doc->tags->addChild('tag', $tag);
		// add query data
		foreach ($request->getParsedQueryData() as $key => $value)
			$doc[$key] = (string) $value;
		
		// create and send the response
		return $this->formatResponse($request, new HTTPResponse(), $doc);
	}
	
	public function DELETE(HTTPRequest $request) {
		// delete this resource
		$this->page->delete();
		
		// get location of parent resource
		$url = clone $request->getURL();
		$url->setComponents(array('query' => null, 'path' => dirname($url->path)));
		// redirect to pages list
		$response = new HTTPResponse();
		$response->setLocation($url);
		return $response;
	}
	
	public function PUT(HTTPRequest $request) {
		// preserve old title
		$oldTitle = $this->page->title;
		
		// update page
		$data = $request->getParsedContent();
		$this->page->update($data['title'], $data['content'], preg_split('/\s+/', $data['tags'], -1, PREG_SPLIT_NO_EMPTY));
		
		// if this is a rename, redirect
		if ($oldTitle != $this->page->title) {
			// redirect to new page
			$url = clone $request->getURL();
			$url->setComponents(array('query' => null, 'path' => dirname($url->path) . '/' . $this->page->title));
			$response = new HTTPResponse();
			$response->setLocation($url);
			return $response;
		}
		
		// show updated page
		return $this->GET($request);
	}
}

?>