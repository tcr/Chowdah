<?php

/**
 * HTTP Status Exception
 * @package chowdah.http
 */

#[TODO] allow customization
#[TODO] pass request object to getHTTPResponse()
#[TODO] don't embed password in error page!

class HTTPStatusException extends Exception {
	// HTTP response
	protected $response;
	
	function __construct($code, $message = null, $extendedMessage = null, $headers = array()) {
		// set the message and code
		parent::__construct($message, $code);

		// get the current request
		$request = HTTPRequest::getCurrent();
		// create a response
		$this->response = new HTTPResponse();
		$this->response->setStatus($code, $message);
		$this->response->setContent(
'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
 <head>
  <title>'. $this->response->getStatusCode() . ' Error: '. $this->response->getReasonPhrase() . '</title>
 </head>
 <body>
  <h1>'. $this->response->getStatusCode() . ' Error: '. $this->response->getReasonPhrase() . '</h1>
' . ($extendedMessage ? '  <p>' . htmlspecialchars($extendedMessage) . "</p>\n" : '') . '  <hr>
  <p><strong>' . $request->getMethod() . '</strong> on <em>' . $request->getURL()->serialize() . '</em></p>
 </body>
</html>');
		$this->response->setContentType(MIMEType::create('text', 'html'));
		// set the response headers
		foreach ($headers as $header => $value)
			$this->response->setHeader($header, $value);
	}
	
	public function getHTTPResponse() {
		// return the response object
		return $this->response;
	}
}

?>