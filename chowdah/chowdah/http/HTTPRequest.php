<?php

/**
 * HTTP Request
 * @package chowdah.http
 */

class HTTPRequest extends HTTPMessage {
	// message data
	protected $method = 'GET';
	protected $url = null;

	function __construct($method = 'GET', $url = null, $version = 1.0) {
		// set variables
		$this->setMethod($method);
		$this->setURL($url ? $url : new URL());
		$this->setHTTPVersion($version);
		// call parent constructor
		parent::__construct();
	}

	function send($followRedirect = true) {
		// get url components
		$url = $this->getURL();

		// request loop
		do {
			// serialize request line
			$request = sprintf("%s %s HTTP/%03.1f\r\n", $this->getMethod(),
			    $url->path . ($url->query !== null ? '?' . $url->query : ''), $this->getHTTPVersion());
			// serialize headers
			foreach ($this->headers as $key => $header)
				foreach ((array) $header as $index => $value)
					header($key . ': ' . $value, !$index);
			// HTTP 1.1 headers
			if ($this->getHTTPVersion() >= 1.1) {
				// add a Host: header if one isn't set
				if (!$this->getHeader('Host'))
					$request .= 'Host: ' . $url->host . "\r\n";
				// add a Connection: header if one isn't set
				if (!$this->getHeader('Connection'))
					$request .= "Connection: close\r\n";
			}
			// append the content
			$request .= "\r\n" . $this->getContent();

			// create the connection handle
			if (($handle = fsockopen(($url->scheme == 'https' ? 'ssl://' : '') . $url->host,
			    $url->port ? $url->port : 80, $errno, $errstr, 30)) === false)
				throw new Exception('HTTPRequest::send() connection filed [error ' . $errno . ']: ' . $errstr);
			// submit the request
			fwrite($handle, $request);
			// read the response head
			for ($response = ''; substr($response, -4, 4) != "\r\n\r\n"; $response .= fread($handle, 1));

			// check for a redirection
			preg_match('/(?<=\n|^)Location:\s+(.+)\s*?\r\n/i', $response, $matches);
		} while ($followRedirect && $matches[1] && ($url = URL::parse($matches[1])));

		// read response
		if ($this->getHTTPVersion() >= 1.1 && preg_match('/(?<=\n|^)Transfer-Encoding:\s+chunked\s*?\r\n/i', $response)) {
			// parse chunks until we reach the 0-length chunk (end marker)
			while (!feof($handle)) {
				// get the chunk size
				for ($chunkSize = ''; substr($chunkSize, -2, 2) != "\r\n" && !feof($handle); $chunkSize .= fread($handle, 1));
				$chunkSize = hexdec(substr($chunkSize, 0, -2));

				// read the chunk
				for ($chunk = ''; $chunkSize > 0 && !feof($handle); $chunkSize -= strlen($chunk), $response .= $chunk)
					$chunk = fread($handle, $chunkSize);
				// remove trailing CRLF
				fread($handle, 2);
			}

			// remove transfer-encoding header
			$response = preg_replace('/(?<=\n|^)Transfer-Encoding:\s*chunked\s*?\r\n/i', '', $response);
		} else {
			// check for a specified content length
			if (preg_match('/(?<=\n|^)Content-Length:\s*(\d+)\s*?\r\n/i', $response, $matches))
				$length = (int) $matches[1];

			// read the content
			for ($content = ''; !feof($handle) && (!$length || strlen($content) < $length); )
				$content .= fread($handle, $length ? $length - strlen($content) : 4096);
			$response .= $content;
		}

		// close connection
		fclose($handle);
		// return the HTTPResponse object
		return HTTPResponse::parse($response);
	}

	//------------------------------------------------------------------
	// request creation
	//------------------------------------------------------------------

	static public function getCurrent() {
		// create a new request object
		$request = new HTTPRequest($_SERVER['REQUEST_METHOD']);
		
		// set the url
		$request->setURL(new URL(array(
			'scheme' => strtolower(preg_replace('/\/.*$/', '', $_SERVER['SERVER_PROTOCOL'])) .
			    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : ''),
			'host' => $_SERVER['SERVER_NAME'],
			'port' => $_SERVER['SERVER_PORT'],
			'path' => preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']),
			'query' => preg_replace('/^[^?]+\?|^.+$/', '', $_SERVER['REQUEST_URI']),
			'user' => isset($_SERVER['PHP_AUTH_USER']) ?
			    $_SERVER['PHP_AUTH_USER'] : null,
			'pass' => isset($_SERVER['PHP_AUTH_PW']) ?
			    $_SERVER['PHP_AUTH_PW'] : null
		    )));

		// set headers
		if (function_exists('getallheaders')) {
			// set all headers (only available when running as Apache)
			foreach (getallheaders() as $key => $value)
				$request->setHeader($key, $value);
		} else {
			// extract headers from $_SERVER array
			// (entries formatted as HTTP_* are interpreted as HTTP headers)
			foreach (preg_grep('/^HTTP_/i', array_keys($_SERVER)) as $key)
				$request->setHeader(str_replace('_', '-', substr($key, 5)), $_SERVER[$key]);
			if (isset($_SERVER['CONTENT_LENGTH']))
				$request->setHeader('Content-Length', $_SERVER['CONTENT_LENGTH']);
			if (isset($_SERVER['CONTENT_TYPE']))
				$request->setHeader('Content-Type', $_SERVER['CONTENT_TYPE']);

			// authorization variables
			if (isset($_SERVER['PHP_AUTH_DIGEST']))
				$request->setHeader('Authorization', $_SERVER['PHP_AUTH_DIGEST']);
			else if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
				$request->setHeader('Authorization', 'Basic ' .
				    base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']));
		}

		// set the content
		if (isset($_SERVER['HTTP_RAW_POST_DATA'])) {
			// get from HTTP_RAW_POST_DATA variable
			$request->setContent($_SERVER['HTTP_RAW_POST_DATA']);
		} else if (isset($_SERVER['CONTENT_TYPE']) &&
		    MIMEType::parse($_SERVER['CONTENT_TYPE'])->match(new MIMEType('multipart', 'form-data'))) {
			// load the POST data
			$data = strip_magic_quotes($_POST);
			// convert $_FILES array to file object
			foreach ($_FILES as $name => $fileData) {
				// create a virtual document from the data
				$file = new VirtualDocument();
				$file->setPath($name);
				$file->setMIMEType(MIMEType::parse($fileData['type']));
				$file->setContent(file_get_contents($fileData['tmp_name']));
				$data[] = $file;
			}		    
			// reconstruct submitted data
			$request->setParsedContent($data, new MIMEType('multipart', 'form-data'));
		} else if ((int) $_SERVER['CONTENT_LENGTH']) {
			// input from php://input stream
			$request->setContent(file_get_contents('php://input'));
		}

		// return the request
		return $request;
	}

	static public function parse($data) {
		// split the data
		list ($head, $content) = explode("\r\n\r\n", $data, 2);
		// parse the status line
		preg_match('/^(.+?) (.+?) HTTP\/(\d+\.\d+)\r\n/', $head, $heading);
		// parse the headers
		preg_match_all('/(?<=\r\n)([^:]+):\s*(.*?)(?:\r\n|$)/', $head, $headers, PREG_SET_ORDER);

		// load the request into an object
		$request = new HTTPRequest($heading[1], $heading[2], $heading[3]);
		foreach ($headers as $header)
			$request->setHeader($header[1], $header[2], false);
		$request->setContent($content);

		// return the request
		return $request;
	}

	//------------------------------------------------------------------
	// method
	//------------------------------------------------------------------

	public function getMethod() {
		// return the http method
		return $this->method;
	}

	public function setMethod($method) {
		// set the method
		return $this->method = preg_replace('/\s+/', '', $method);
	}

	//------------------------------------------------------------------
	// url
	//------------------------------------------------------------------

	public function getURL() {
		// return the http url
		return $this->url;
	}

	public function setURL(URL $url) {
		// set the url
		return $this->url = $url;
	}

	//------------------------------------------------------------------
	// query
	//------------------------------------------------------------------

	public function getParsedQueryData() {
		// parse the query
		return http_parse_query($this->getURL()->query);
	}

	public function setParsedQueryData($data) {
		// build the query
		$this->getURL()->query = http_build_query($data);
		return $this->getURL()->query;
	}

	//------------------------------------------------------------------
	// accept headers
	//------------------------------------------------------------------

#[TODO] shrink all this somehow!

	protected function getAcceptedTypes($class, $header) {
		return array_filter(array_map(array($class, 'parse'),
		    preg_split('/[\s,]/', $this->getHeader($header), -1, PREG_SPLIT_NO_EMPTY)));
	}

	public function setAcceptedTypes($class, $header, $accepted) {
		// type check arguments
		foreach ($accepted as $index => $type)
			if (!($type instanceof $class))
				array_splice($accepted, $index, 1);
		
		// clear and set the header
		$this->deleteHeader($header);
		foreach ($accepted as $type)
			$this->setHeader($header, $type->serialize(true), false);
	}

	protected function negotiateType($class, $header, $available, $default = null) {
		// type check arguments
		if ($default && !($default instanceof $class))
			$default = false;
		
		// if no accept header was sent, use default
		if (!$this->getHeader($header))
			return $default;
		// else, find best type matches
		$matches = call_user_func(array($class, findBestMatches),
		    $this->getAcceptedTypes($class, $header), $available);
		// return the best match, or return false (406 Not Acceptable)
		return count($matches) ? $matches[0] : false;
	}
	
	// MIME type
	
	public function getAcceptedMIMETypes() {
		return $this->getAcceptedTypes('MIMEType', 'Accept');
	}
	
	public function setAcceptedMIMETypes($accepted) {
		$this->setAcceptedTypes('MIMEType', 'Accept', $accepted);
	}
	
	public function deleteAcceptedMIMETypes() {
		$this->deleteHeader('Accept');
	}
	
	public function negotiateContentType($available, $default = null) {
		return $this->negotiateType('MIMEType', 'Accept', $available, $default);
	}

	// Encoding
	
	public function getAcceptedEncodings() {
		return $this->getAcceptedTypes('EncodingType', 'Accept-Encoding');
	}
	
	public function setAcceptedEncodings($accepted) {
		$this->setAcceptedTypes('EncodingType', 'Accept-Encoding', $accepted);
	}
	
	public function deleteAcceptedEncodings() {
		$this->deleteHeader('Accept-Encoding');
	}
	
	public function negotiateContentEncoding($available, $default = null) {
		return $this->negotiateType('EncodingType', 'Accept-Encoding', $available, $default);
	}

	// Charset
	
	public function getAcceptedCharsets() {
		// get accepted charsets, and add default ISO-8859-1 if not specified
		$types = $this->getAcceptedTypes('CharsetType', 'Accept-Charset');
		if (!CharsetType::create('ISO-8859-1')->match($types))
			$types[] = CharsetType::create('ISO-8859-1', array('q' => 1));
		return $types;
	}
	
	public function setAcceptedCharsets($accepted) {
		$this->setAcceptedTypes('CharsetType', 'Accept-Charset', $accepted);
	}
	
	public function deleteAcceptedCharsets() {
		$this->deleteHeader('Accept-Charset');
	}
	
	public function negotiateContentCharset($available, $default = null) {
		return $this->negotiateType('CharsetType', 'Accept-Charset', $available, $default);
	}

	// Language
	
	public function getAcceptedLanguages() {
		return $this->getAcceptedTypes('LanguageType', 'Accept-Language');
	}
	
	public function setAcceptedLanguages($accepted) {
		$this->setAcceptedTypes('LanguageType', 'Accept-Language', $accepted);
	}
	
	public function deleteAcceptedLanguages() {
		$this->deleteHeader('Accept-Language');
	}
	
	public function negotiateContentLanguage($available, $default = null) {
		return $this->negotiateType('LanguageType', 'Accept-Language', $available, $default);
	}

	//----------------------------------------------------------------------
	// authentication
	//----------------------------------------------------------------------
	
	public function verifyAuthentication($callback) {
		// get the authorization header
		if (!($auth = $this->getHeader('Authorization')))
			return false;
		
		// parse the header
		$type = null;
		$args = array();
		if (preg_match('/^\s*Basic\s+(\S+)$/i', $auth, $matches)) {
			// Basic authentication
			$type = 'Basic';
			list ($args['username'], $args['password']) = explode(':', base64_decode($matches[1]), 2);
		} else if (preg_match('/^\s*Digest\s+(\S+)$/i', $auth, $matches)) {
			#[TODO] authentication
		}
		
		// call the callback
		return call_user_func($callback, $type, $args);
	}
	
	#[TODO] further do this. setAuthorization(), authorize()

	//------------------------------------------------------------------
	// variable overloading
	//------------------------------------------------------------------

	function __get($key) {
		switch ($key) {
			case 'url':
				return $this->getURL();
				
			case 'parsedQueryData':
				return $this->getParsedQueryData();
				
			default:
				return parent::__get($key);
		}
	}

	function __set($key, $value) {
		switch ($key) {
			case 'parsedQueryData':
				return $this->setParsedQueryData($value);
				
			default:
				return parent::__set($key, $value);
		}
	}
}

?>