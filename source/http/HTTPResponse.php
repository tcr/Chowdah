<?php

/**
 * HTTP Response
 * @package HTTP
 */

class HTTPResponse extends HTTPMessage {
	// message data
	protected $statusCode = 200;
	protected $statusMessage = 'OK';

	function __construct($version = null, $statusCode = null, $statusMessage = null) {
		// set variables
		if ($version !== null)
			$this->setHTTPVersion($version);
		if ($statusCode !== null)
			$this->setStatus($statusCode, $statusMessage);
		else if ($statusMessage !== null)
			$this->setStatusMessage($statusMessage);
		// call parent constructor
		parent::__construct();
	}

	public function send() {
		#[HACK] send IE WWW-Authenticate header first
		if ($this->getHeader('WWW-Authenticate'))
			header('WWW-Authenticate: ' . $this->getHeader('WWW-Authenticate'));
		
		// submit the status-line
		header(sprintf('HTTP/%03.1f %03.0d %s', $this->getHTTPVersion(),
		    $this->getStatusCode(), $this->getStatusMessage()));
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

	public function getStatusMessage() {
		// return the http status message
		return $this->statusMessage;
	}

	public function setStatusMessage($message) {
		// set the http status message
		return $this->statusMessage = preg_replace('/[\n\r]/', '', $message);
	}

	// shortcut to set complete status
	public function setStatus($code, $message = null) {
		// set the code
		$this->setStatusCode($code);
		// set status text
		$this->setStatusMessage($message === null ? HTTPResponse::$DEFAULT_STATUS_TEXT[$code] : $message);
	}
	
	// status constants
	const STATUS_CONTINUE = 100;
	const STATUS_SWITCHING_PROTOCOLS = 101;
	const STATUS_PROCESSING = 102;
	const STATUS_OK = 200;
	const STATUS_CREATED = 201;
	const STATUS_ACCEPTED = 202;
	const STATUS_NON_AUTHORITATIVE_INFORMATION = 203;
	const STATUS_NO_CONTENT = 204;
	const STATUS_RESET_CONTENT = 205;
	const STATUS_PARTIAL_CONTENT = 206;
	const STATUS_MULTI_STATUS = 207;
	const STATUS_IM_USED = 226;
	const STATUS_MULTIPLE_CHOICES = 300;
	const STATUS_MOVED_PERMANENTLY = 301;
	const STATUS_FOUND = 302;
	const STATUS_SEE_OTHER = 303;
	const STATUS_NOT_MODIFIED = 304;
	const STATUS_USE_PROXY = 305;
	const STATUS_SWITCH_PROXY = 306;
	const STATUS_TEMPORARY_REDIRECT = 307;
	const STATUS_BAD_REQUEST = 400;
	const STATUS_UNAUTHORIZED = 401;
	const STATUS_PAYMENT_REQUIRED = 402;
	const STATUS_FORBIDDEN = 403;
	const STATUS_NOT_FOUND = 404;
	const STATUS_METHOD_NOT_ALLOWED = 405;
	const STATUS_NOT_ACCEPTABLE = 406;
	const STATUS_PROXY_AUTHENTICATION_REQUIRED = 407;
	const STATUS_REQUEST_TIMEOUT = 408;
	const STATUS_CONFLICT = 409;
	const STATUS_GONE = 410;
	const STATUS_LENGTH_REQUIRED = 411;
	const STATUS_PRECONDITION_FAILED = 412;
	const STATUS_REQUEST_ENTITY_TOO_LARGE = 413;
	const STATUS_REQUEST_URI_TOO_LONG = 414;
	const STATUS_UNSUPPORTED_MEDIA_TYPE = 415;
	const STATUS_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	const STATUS_EXPECTATION_FAILED = 417;
	const STATUS_UNPROCESSABLE_ENTITY = 422;
	const STATUS_LOCKED = 423;
	const STATUS_FAILED_DEPENDENCY = 424;
	const STATUS_UPGRADE_REQUIRED = 426;
	const STATUS_INTERNAL_SERVER_ERROR = 500;
	const STATUS_NOT_IMPLEMENTED = 501;
	const STATUS_BAD_GATEWAY = 502;
	const STATUS_SERVICE_UNAVAILABLE = 503;
	const STATUS_GATEWAY_TIMEOUT = 504;
	const STATUS_HTTP_VERSION_NOT_SUPPORTED = 505;
	const STATUS_REDIRECTION_FAILED = 506;
	const STATUS_INSUFFICIENT_STORAGE = 507;
	const STATUS_NOT_EXTENDED = 510;

	// pseudo-class constant (arrays can't be class constants)
	static $DEFAULT_STATUS_TEXT = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		226 => 'IM Used',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Switch Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		426 => 'Upgrade Required',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Redirection Failed',
		507 => 'Insufficient Storage',
		510 => 'Not Extended'
	    );

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