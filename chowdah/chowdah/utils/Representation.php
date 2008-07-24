<?php

class Representaton
{
	private $defaultRepresentation = null;
	private $representations = array();
	private $callbacks = array();
	
	public registerType(MIMEType $type, $callback, $default = false)
	{
		$this->representations[] = $type;
		$this->callbacks[$type->serialize()] = $callback;
		if ($default)
			$this->defaultRepresentation = $type;
	}
   
	public function getRepresentation(MIMEType $type)
	{
		// call the type handler
		if ($callback = $this->callbacks[$type->serialize()])
			return $callback($type, $request);
		// no available callback
		return false;
	}
   
	public function createResponse(HTTPRequest $request)
	{
		// create the HTTP response
		if ($type = $request->negotiateContentType($this->representations, $defaultRepresentation))
		{
			// create the response
			$response = new HTTPResponse();
			$response->setParsedContent($this->getRepresentation($type), $type);
			return $response;
		}
		else
		{
			// no acceptable response could be created
			throw new HTTPStatusException(HTTPStatus::NOT_ACCEPTABLE);
		}
	}
}

?>