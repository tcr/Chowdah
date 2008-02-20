<?php

class StripsResource extends MacheResourceBase implements Collection {
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	protected $methods = array('GET', 'OPTIONS', 'POST');
	
	public function GET(HTTPRequest $request) {
		// format query parameters
		$search = array(
			'title' => $request->parsedQueryData['title'],
			'tags' => preg_split('/\s+/', $request->parsedQueryData['tags'], null, PREG_SPLIT_NO_EMPTY),
			'content' => $request->parsedQueryData['content']
		    );
		
		// create an XML respresentation of this object
		$doc = new SimpleXMLElement('<strips />');
		foreach (Strip::getList($search) as $strip) {
			$node = $doc->addChild('strip');
			$node->addChild('title', $strip->title);
			$node->addChild('id', $strip->id);
			$node->addChild('tags');
			foreach ($strip->tags as $tag)
				$node->tags->addChild('tag', $tag);
		}
		// add query data
		foreach ($request->getParsedQueryData() as $key => $value)
			$doc[$key] = (string) $value;
		
		// create and send the response
		return $this->formatResponse($request, new HTTPResponse(), $doc);
	}

	public function POST(HTTPRequest $request) {
		// create new strip
		$data = $request->getParsedContent();
		$strip = Strip::create($data['title'], $data['content'], preg_split('/\s+/', $data['tags'], null, PREG_SPLIT_NO_EMPTY));

		// get location of the new strip
		$url = clone $request->getURL();
		$url->setComponents(array('query' => null, 'path' => $url->path . $strip->id));
		// redirect to new strip
		$response = new HTTPResponse();
		$response->setLocation($url);
		return $response;
	}

	//----------------------------------------------------------------------
	// collection
	//----------------------------------------------------------------------

	public function getChild($id) {
		// get the child user
		try {
			return new StripResource(new Strip($id));
		} catch (Exception $e) { }
		return false;
	}
}

?>