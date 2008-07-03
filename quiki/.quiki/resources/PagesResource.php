<?php

class PagesResource extends QuikiResourceBase implements ICollection
{
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	protected $methods = array('GET', 'OPTIONS', 'POST');
	
	public function GET(HTTPRequest $request)
	{
		// format query parameters
		$search = array(
			'title' => $request->parsedQueryData['title'],
			'tags' => preg_split('/\s+/', $request->parsedQueryData['tags'], null, PREG_SPLIT_NO_EMPTY),
			'content' => $request->parsedQueryData['content']
		    );
		
		// create an XML respresentation of this object
		$doc = new SimpleXMLElement('<pages />');
		foreach (Page::getList($search) as $page)
		{
			$node = $doc->addChild('page');
			$node->addChild('title', $page->title);
			$node->addChild('tags');
			foreach ($page->tags as $tag)
				$node->tags->addChild('tag', $tag);
		}
		// add query data
		foreach ($request->getParsedQueryData() as $key => $value)
			$doc[$key] = (string) $value;
		
		// create and send the response
		return $this->formatResponse($request, new HTTPResponse(), $doc);
	}

	public function POST(HTTPRequest $request)
	{
		// create new page
		$data = $request->getParsedContent();
		$page = Page::create($data['title'], $data['content'], preg_split('/\s+/', $data['tags'], null, PREG_SPLIT_NO_EMPTY));
#[TODO] what happens when this fails?

		// get location of the new page
		$url = clone $request->getURL();
		$url->setComponents(array('query' => null, 'path' => $url->path . rawurlencode($page->title)));
		// redirect to new page
		$response = new HTTPResponse();
		$response->setLocation($url);
		return $response;
	}

	//----------------------------------------------------------------------
	// collection
	//----------------------------------------------------------------------

	public function getChild($page) {
		try {
			// get the child page
			return new PageResource(new Page($page));
		}
		catch (Exception $e)
		{
			// allow user to create page
			return new PageNotFoundResource($page);
		}
	}
}

?>