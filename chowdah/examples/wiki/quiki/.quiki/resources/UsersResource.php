<?php

class UsersResource extends QuikiResourceBase implements ICollection
{
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	protected $methods = array('GET', 'POST', 'OPTIONS');

	public function GET(HTTPRequest $request)
	{
		// create the response
		$response = new HTTPResponse();

		// determine what mode to display
		if (!isset($request->parsedQueryData['register'])) {
			// create a users list
			$doc = new SimpleXMLElement('<users />');
			// add users
			foreach (User::getList() as $user) {
				$node = $doc->addChild('user');
				$node->name = $user->name;
			}
		} else if (Quiki::getAuthenticatedUser($request)) {
			// user cannot register when logged in
			$response->setStatus(401);
			$doc = new SimpleXMLElement('<registration authorized="false" />');
		} else {
			// display registration page
			$doc = new SimpleXMLElement('<registration />');
		}
		// convert the document
		$doc = dom_import_simplexml($doc)->ownerDocument;
		
		// create and send the response
		return $this->formatResponse($request, $response, $doc);
	}
	
	public function POST(HTTPRequest $request)
	{
		// create the response
		$response = new HTTPResponse();

		// validate submitted data
		if (!is_array($request->parsedContent))
			throw new HTTPStatusException(400, null, 'The submitted data was in an unparsable format.');

		// user cannot register when logged in
		if (Quiki::getAuthenticatedUser($request)) {
			$response->setStatus(401);
			$doc = new SimpleXMLElement('<registration authorized="false" />');
			$doc = dom_import_simplexml($doc)->ownerDocument;
			return $this->formatResponse($request, $response, $doc);
		}

		// account creation
		try {
			// validate username
			if (!isset($request->parsedContent['name']) ||
			    !preg_match('/^[a-z_][a-z_\-0-9]{0,254}$/', $request->parsedContent['name']))
				throw new Exception('Please enter a valid username containing a maximum of 256 alphanumeric characters.');
			$name = $request->parsedContent['name'];
			// validate email
			if (!isset($request->parsedContent['email']) ||
			    !strlen($request->parsedContent['email']))
				throw new Exception('Please enter a valid e-mail address.');
			$email = $request->parsedContent['email'];
			
			// create user
			$password = User::register($name, $email);
			
			// display confirmation message
			$doc = new SimpleXMLElement('<registration success="true" />');
			$doc->name = $name;
			$doc->email = $email;
		} catch (Exception $e) {
			// encountered an error
			$doc = new SimpleXMLElement('<registration />');
			$doc->name = $request->parsedContent['name'];
			$doc->email = $request->parsedContent['email'];
			$doc->error = $e->getMessage();
		}
		// convert the document
		$doc = dom_import_simplexml($doc)->ownerDocument;

		// send the response
		return $this->formatResponse($request, $response, $doc);
	}
	
	//----------------------------------------------------------------------
	// collection
	//----------------------------------------------------------------------
	
	public function getChild($name)
	{
		// get the user resource
		try {
			return new UserResource(new User($name));
		} catch (Exception $e) { }
		return false;
	}
}

?>