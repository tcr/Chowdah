<?php

/**
 * HTTP Response
 * @package chowdah.http
 */

class HTTPResponse extends HTTPMessage {
	// message data
	protected $statusCode = 200;
	protected $reasonPhrase = 'OK';

	function __construct($version = 1.0, $statusCode = 200, $reasonPhrase = null) {
		// set variables
		$this->setHTTPVersion($version);
		$this->setStatus($statusCode, $reasonPhrase);
		// call parent constructor
		parent::__construct();
	}

	public function send() {
		#[HACK] send IE WWW-Authenticate header first
		if ($this->getHeader('WWW-Authenticate'))
			header('WWW-Authenticate: ' . $this->getHeader('WWW-Authenticate'));
		
		// submit the status-line
		header(sprintf('HTTP/%03.1f %03.0d %s', $this->getHTTPVersion(),
		    $this->getStatusCode(), $this->getReasonPhrase()));
		// submit the headers
		foreach ($this->headers as $key => $header)
			foreach ((array) $header as $index => $value)
				header($key . ': ' . $value, !$index);
		// output the content
		echo $this->content;
	}

	//------------------------------------------------------------------
	// response creation
	//------------------------------------------------------------------

	static public function getCurrent() {
		// cache the current response
		static $response = null;
		if ($response !== null)
			return $response;

		// create the HTTPRespone object
		$response = new HTTPResponse();

		// add headers to be sent
		foreach (headers_list() as $header)
			$response->setHeader(preg_replace('/:.*$/', '', $header),
			    preg_replace('/^[^:]+?:\s*/', '', $header), false);

		// return the response
		return $response;
	}

	static public function parse($data) {
		// split the data
		list ($head, $content) = explode("\r\n\r\n", $data, 2);
		// parse the status line
		preg_match('/^HTTP\/(\d+\.\d+) (\d+) (.*?)\r\n/', $head, $heading);
		// parse the headers
		preg_match_all('/(?<=\r\n)([^:]+):\s*(.*?)(?:\r\n|$)/', $head, $headers, PREG_SET_ORDER);

		// load the response into an object
		$response = new HTTPResponse($heading[1], $heading[2], $heading[3]);
		foreach ($headers as $header)
			$response->setHeader($header[1], $header[2], false);
		$response->setContent($content);

		// return the response
		return $response;
	}

	//------------------------------------------------------------------
	// status
	//------------------------------------------------------------------

	public function getStatusCode() {
		// return the http status code
		return $this->statusCode;
	}

	public function setStatusCode($code) {
		// set the http status code
		return $this->statusCode = (int) $code;
	}

	public function getReasonPhrase() {
		// return the http status message
		return $this->reasonPhrase;
	}

	public function setReasonPhrase($message) {
		// set the http status message
		return $this->reasonPhrase = preg_replace('/[\n\r]/', '', $message);
	}

	// shortcut to set complete status
	public function setStatus($code, $reason = null) {
		// set the code
		$this->setStatusCode($code);
		// set status text
		$this->setReasonPhrase($reason === null ? HTTPStatus::getReasonPhrase($code) : $reason);
	}

	//------------------------------------------------------------------
	// cookies
	//------------------------------------------------------------------

	public function setCookie($name, $value, $options = array()) {
		// serialize the parameters
		extract($options, EXTR_SKIP);
		$params = ($expires === null ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s \G\M\T', $expires)) .
		    ($path === null ? '' : '; path=' . $path) .
		    ($domain === null ? '' : '; domain=' . $domain) .
		    ($secure ? '; secure' : '') .
		    ($httpOnly ? '; httpOnly' : '');
		// set the cookie
		$this->cookies->offsetSet($name, $value, $params);
	}

	//----------------------------------------------------------------------
	// authentication
	//----------------------------------------------------------------------
	
	public function requestAuthentication($type, $paramHash = array(), $unauthorized = true) {
		// set the unauthorized header
		if ($unauthorized)
			$this->setStatus(401);
	
		// format params
		$params = array();
		foreach ($paramHash as $key => $value)
			$params[] = $key . '="' . $value . '"';
		// request authentication
		$this->setHeader('WWW-Authenticate', ucwords($type) . ' ' . implode(', ', $params));		
		return true;
	}

	//----------------------------------------------------------------------
	// location
	//----------------------------------------------------------------------
	
	public function getLocation() {
		return URL::parse($this->getHeader('Location'));
	}
	
	public function setLocation(URL $url, $status = null) {
		// set the location header
		$this->setHeader('Location', $url->serialize());
		// set the redirection status if specified
		if ($status !== null)
			$this->setStatus($status);
	}
	
	public function deleteLocation() {
		$this->deleteHeader('Location');
	}
}

?>