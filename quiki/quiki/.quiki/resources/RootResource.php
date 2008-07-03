<?php 

class RootResource extends QuikiResourceBase implements ICollection
{
	//----------------------------------------------------------------------
	// construction
	//----------------------------------------------------------------------
	
	protected $collection;

	function __construct() {
		// get the internal collection object
		$this->collection = new CollectionResource(new FSCollection('..'), false);
	}
	
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	protected $methods = array('GET', 'OPTIONS');
	
	public function GET(HTTPRequest $request)
	{
		// redirect to the main page
		$url = clone $request->getURL();
		$url->setComponents(array('query' => null, 'path' => $url->path . 'pages/' . rawurlencode('Main Page')));
		$response = new HTTPResponse();
		$response->setLocation($url);
		return $response;
	}

	//----------------------------------------------------------------------
	// collection
	//----------------------------------------------------------------------

	public function getChild($filename) {
		// get the child resource
		switch ($filename)
		{
		    case 'login': return new LoginResource();
		    case 'pages': return new PagesResource();
		    case 'users': return new UsersResource();
		    default: return $this->collection->getChild($filename);
		}
	}
}

?>