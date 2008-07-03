<?php

class LoginResource extends QuikiResourceBase
{
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	protected $methods = array('GET', 'OPTIONS');

	public function GET(HTTPRequest $request)
	{
		// create response
		$response = new HTTPResponse();
		
		// check for credentials
		if (!Quiki::getAuthenticatedUser($request))
		{
			// request credentials
			$response->requestAuthentication('Basic', array('realm' => 'Quiki'));
			
			// create an XML representation of this object
			$doc = new SimpleXMLElement('<login />');
			return $this->formatResponse($request, $response, $doc);
		}
		else
		{
			// user is logged in, go to home page
			$url = clone $request->getURL();
			$url->setComponents(array('query' => null, 'path' =>
			    isset($request->parsedQueryData['redirect']) ?
				$request->parsedQueryData['redirect'] : '/'));
			$response->setLocation($url, 302);
			return $response;
		}
	}
}

?>