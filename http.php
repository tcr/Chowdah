<?php

//##############################################################################
// HTTP Classes for PHP5 (http.php)
// allows manipulation of HTTP requests and responses
//##############################################################################
// written by Tim Ryan (tim-ryan.com)
// released under the MIT/X License
// we are not hackers!
//##############################################################################

//==============================================================================
// HTTPMessage
//------------------------------------------------------------------------------
// see: http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4
//==============================================================================

abstract class HTTPMessage {
	// message data
	protected $version = 1.0;
	protected $headers;
	protected $content = null;
	// cookies array
	protected $cookies;

	function __construct() {
		// initialize array objects
		$this->headers = new HTTPHeaderArray(array());
		$this->cookies = new HTTPCookieArray($this);
	}

	// abstract functions
	abstract public function send();
	abstract static public function getCurrent();
	abstract static public function parse($data);

	//----------------------------------------------------------------------
	// HTTP version
	//----------------------------------------------------------------------

	public function getHTTPVersion() {
		// return the http version
		return $this->version;
	}

	public function setHTTPVersion($version) {
		// set the http version
		return $this->version = (float) $version;
	}

	//----------------------------------------------------------------------
	// headers
	//----------------------------------------------------------------------

	public function getHeader($key) {
		return isset($this->headers[$key]) ? $this->headers[$key] : false;
	}

	public function setHeader($key, $value, $overwrite = true) {
		return $this->headers->offsetSet($key, $value, $overwrite);
	}

	public function deleteHeader($key) {
		unset($this->headers[$key]);
	}

	//----------------------------------------------------------------------
	// content
	//----------------------------------------------------------------------

	public function getContent() {
		// return the content
		return $this->content;
	}

	public function setContent($content, $encoding = null) {
		// set the content
		$this->content = $content !== null ? (string) $content : null;

		// set encoding type
		if ($encoding && $encoding->type != 'identity')
			$this->setHeader('Content-Encoding', $encoding->serialize(false));
		else
			$this->deleteHeader('Content-Encoding');

		// set content length
		if (strlen($content))
			$this->setHeader('Content-Length', strlen($content));
		else
			$this->deleteHeader('Content-Length');

		// set MD5
		if ($this->getHeader('Content-MD5'))
			$this->generateMD5Digest();
	}

	public function deleteContent() {
		// clear content
		$this->content = null;
		// clear content headers
		$this->deleteHeader('Content-Encoding');
		$this->deleteHeader('Content-Length');
		$this->deleteHeader('Content-MD5');
	}

	public function appendContent() {
		$args = (array) func_get_args();
		return $this->content .= implode('', $args);
	}

	public function prependContent() {
		$args = (array) func_get_args();
		return $this->content = implode('', $args) . $this->content;
	}

	//----------------------------------------------------------------------
	// content type
	//----------------------------------------------------------------------

	public function getContentType() {
		if ($this->getHeader('Content-Type'))
			return MIMEType::parse($this->getHeader('Content-Type'));
		else
			return MIMEType::create('application', 'octet-stream');
	}

	public function setContentType(MIMEType $mimetype) {
		return $this->setHeader('Content-Type', $mimetype->serialize(true));
	}

	public function deleteContentType() {
		$this->deleteHeader('Content-Type');
	}

	//----------------------------------------------------------------------
	// content language
	//----------------------------------------------------------------------

	public function getContentLanguage() {
		$lang = $this->getHeader('Content-Language');
		return strpos($lang, ',') !== false ? preg_split('/[\s,]+/', $lang) : $lang;
	}

	public function setContentLanguage($lang) {
		return $this->setHeader('Content-Language', implode(', ', (array) $lang));
	}

	public function deleteContentLanguage() {
		$this->deleteHeader('Content-Language');
	}

	//----------------------------------------------------------------------
	// content ranges
	//----------------------------------------------------------------------

	public function getContentRange() {
		// split ranges
		$rangeArray = preg_split('/[\s,]+/', (string) $this->getHeader('Content-Range'));

		// iterate ranges
		$ranges = array();
		foreach ($rangeArray as $rangeString) {
			// parse the range
			if (!preg_match('/^\s*bytes (?P<range>(?P<start>\d+)-(?P<end>\d+)|\*)' .
			    '\/(?P<length>(\d+)|\*)\s*$/', $rangeString, $matches))
				continue;

			// prevent invalid ranges
			extract($matches);
			if (($range != '*' && $start < $end) || ($length != '*' && $length <= $end))
				continue;
			// add it to the range array
			$ranges[] = (object) $matches;
		}

		// return ranges
		return $ranges;
	}

#[TODO] setContentRange

	public function deleteContentRange() {
		$this->deleteHeader('Content-Range');
	}

	//----------------------------------------------------------------------
	// content location
	//----------------------------------------------------------------------

	public function getContentLocation() {
		return URL::parse($this->getHeader('Content-Location'));
	}

	public function setContentLocation(URL $url) {
		return $this->setHeader('Content-Location', $url->serialize());
	}

	public function deleteContentLocation() {
		$this->deleteHeader('Content-Location');
	}

	//----------------------------------------------------------------------
	// content md5
	//----------------------------------------------------------------------

	public function generateMD5Digest() {
		// set the Content-MD5 header
		return $this->getContent() === null ? false :
		    $this->setHeader('Content-MD5', base64_encode(md5($this->getContent(), true)));
	}
	
	public function deleteMD5Digest() {
		$this->deleteHeader('Content-MD5');
	}

	//----------------------------------------------------------------------
	// content encoding
	//----------------------------------------------------------------------

	public function getEncodedContent(EncodingType $type) {
		// get the decoded content
		if (($content = $this->getDecodedContent()) === false)
			return false;

		// encode and return the content
		switch ($encodingtype->serialize(false)) {
			case 'gzip':
				return gzencode($content);
				break;

			case 'deflate':
				return gzdeflate($content, $encodingtype->params['level']);
				break;

			case 'identity':
				return $content;

			default:
				return false;
		}
	}
	
	public function getDecodedContent() {
		// check that there is content to decode
		if ($this->getContent() === null);
			return false;
		// check if there is any encoding
		if (!$this->getHeader('Content-Encoding'))
			return $this->getContent();
		// check that any specified encoding is valid
		if (!($encoding = EncodingType::parse($this->getHeader('Content-Encoding'))))
			return false;
		
		// revert any encoding applied to the content
		switch ($encoding->type) {
			case 'gzip':
				return gzinflate(substr($this->getContent(), 10, -4));

			case 'deflate':
				return gzuncompress(gzinflate($this->getContent()));

			case 'identity':
			default:
				return $this->getContent();
		}
	}

#[TODO] support multiple encodings

	public function encodeContent($accepted = array()) {
		// attempt to get the raw content data
		if (!($content = $this->decodeContent()))
			return false;

		// get the most preferred, available compression
		$encodingtypes = EncodingType::findBestMatches((array) $accepted, array(
			EncodingType::create('gzip'),
			EncodingType::create('deflate')
		    ));
		if (!($encodingtype = $encodingtypes[0]))
			$encodingtype = EncodingType::create('identity');

		// set and return the encoded data
		if (($content = $this->getEncodedContent($encodingtype)) !== false)
			return $this->setContent($content, $encodingtype);
		else
			return false;
	}

	public function decodeContent() {
		// set and return the decoded data
		if (($content = $this->getDecodedContent()) !== false)
			return $this->setContent($content);
		else
			return false;
	}

	//----------------------------------------------------------------------
	// parsed content
	//----------------------------------------------------------------------

	public function getParsedContent() {
		// return a parsed representation of the content (based on MIME type)
		switch ($this->getContentType()->serialize(false)) {
			case 'application/x-www-form-urlencoded':
				// parse the content
				return http_parse_query($this->getContent());

			case 'multipart/form-data':
				// return the parsed form data
				$data = array();

				// get the sections
				$sections = preg_split('/\r\n--' . preg_quote($this->getContentType()->params['boundary']) .
				    '(\r\n|--$)/', $this->getContent(), -1, PREG_SPLIT_NO_EMPTY);
				// parse each section
				foreach ($sections as $section) {
					// split the header and body
					list ($head, $content) = explode('/\r\n\r\n/', $section, 2);
					// get the headers
					preg_match_all('/(?<=^|\n)([^:]+):\s*(.*?)(?:\r\n|$)/s', $head, $matches, PREG_PATTERN_ORDER);
					$headers = array_change_key_case((array) array_combine($matches[1], $matches[2]), CASE_LOWER);

					// parse the disposition header
					if (!preg_match('/form-data\s*(.*)$/s', $headers['content-disposition'], $matches))
						continue;
					preg_match_all('/\s*;\s*([^=]+)\s*=\s*"((?<=")[^"]*(?=")|[^;]+)/',
					    $matches[1], $matches, PREG_PATTERN_ORDER);
					$disposition = (array) array_combine($matches[1], $matches[2]);

					// parse the content
					if (strlen($disposition['filename'])) {
						// entry is a file, so create a virtual document
						$file = new VirtualDocument();
						$file->setContents($content);
						$file->setMIMEType($headers['content-type'] ?
						    MIMEType::parse($headers['content-type']) : MIMEType::create('text', 'plain'));
						$file->setPath($disposition['filename']);
						// save the entry
						$data[$disposition['name']] = $file;
					} else
						// entry is a data value
						$data[$disposition['name']] = $content;
				}

				return $data;

			case 'text/xml':
			case 'application/xml':
			case 'application/xhtml+xml':
				return DOMDocument::loadXML($this->getContent());

			case 'text/html':
				return DOMDocument::loadHTML($this->getContent());

			default:
				return $this->getContent();
		}
	}

	public function setParsedContent($data, $type = null) {
		// get the content mimetype
		if ($type)
			$this->setContentType($type);
		else
			$type = $this->getContentType();

		// set a parsed representation of the content (based on MIME type)
		switch ($type->serialize(false)) {
			case 'application/x-www-form-urlencoded':
				// convert the data
				$this->setContent(http_build_query($data));
				break;

			case 'multipart/form-data':
				// reconstruct from submitted data
				$this->setContent('');
				// get the boundary key, or generate one
				$boundary = isset($type->params['boundary']) ? $type->params['boundary'] :
				    '-----------------------=_' . mt_rand();
				    
				// iterate the form data
				foreach ((array) $data as $name => $entry) {
					// check the type of entry
					if ($entry instanceof Document) {
						// add the file data
						$this->appendContent('--' . $boundary . "\r\n" .
						    'Content-Disposition: form-data; name="' . $name . '";' .
						    'filename="' . urlencode($entry->getFilename()) . "\"\r\n" .
						    'Content-Type: ' . $entry->getMIMEType()->serialize() . "\r\n\r\n" .
						   $entry->getContent() . "\r\n");
					} else {
						// add the form data
						foreach (explode('&', http_build_query($entry, '', '&')) as $value) {
							list ($name, $value) = explode('=', $value);
							$this->appendContent('--' . $boundary . "\r\n" .
							    'Content-Disposition: form-data;name="' . $name . "\"\r\n\r\n" .
							    $value . "\r\n");
						}
					}
				}	
				// end multipart data
				$this->appendContent('--' . $boundary . "--\r\n");
				break;

			case 'text/xml':
			case 'application/xml':
			case 'application/xhtml+xml':
				$data->formatOutput = true;
				$this->setContent($data->saveXML());
				break;

			case 'text/html':
				$data->formatOutput = true;
				$this->setContent($data->saveHTML());
				break;

			default:
				$this->setContent($data);
				break;
		}
	}

	//----------------------------------------------------------------------
	// content as document
	//----------------------------------------------------------------------
	
	public function getContentAsDocument() {
		// create a virtual document of the entity
		$document = new VirtualDocument();
		if ($this instanceof HTTPRequest)
			$document->setPath(basename($this->getURL()->path));
		$document->setContentType($this->getContentType());
		$document->setContent($this->getDecodedContent());
		return $document;
	}

	public function setContentAsDocument(Document $document) {
		// load the document content as a message entity
		$this->setContentType($document->getContentType());
		$this->setContent($document->getContent());
	}

	//----------------------------------------------------------------------
	// cookies
	//----------------------------------------------------------------------

	public function getCookie($name) {
		return $this->cookies[$name];
	}

	public function setCookie($name, $value) {
		return $this->cookies[$name] = $value;
	}
	
	public function deleteCookie($name) {
		unset($this->cookies[$name]);
	}

	//----------------------------------------------------------------------
	// user agent
	//----------------------------------------------------------------------
	
	public function getUserAgent() {
		return $this->getHeader('User-Agent');
	}

	public function setUserAgent($agent) {
		return $this->setHeader('User-Agent', $agent);
	}

	public function deleteUserAgent() {
		$this->deleteHeader('User-Agent');
	}
	
	public function getUserAgentInfo() {
#[TODO] use workarounds?
		return @get_browser($this->getUserAgent());
	}
	
	//----------------------------------------------------------------------
	// variable overloading
	//----------------------------------------------------------------------

	function __get($key) {
		switch ($key) {
			case 'headers':
				return $this->headers;

			case 'cookies':
				return $this->cookies;
			
			case 'parsedContent':
				return $this->getParsedContent();
		}
	}

	function __set($key, $value) {
		switch ($key) {
			case 'parsedContent':
				return $this->setParsedContent($value);
		}
	}
}

//--------------------------------------------------------------------------
// headers array object
//--------------------------------------------------------------------------

class HTTPHeaderArray extends ArrayObject {
	public function offsetExists($index) {
		return parent::offsetExists(strtolower($index));
	}

	public function offsetGet($index) {
		return parent::offsetGet(strtolower($index));
	}

	public function offsetSet($index, $newval, $overwrite = true) {
		// validate the index
		if (!strlen($index))
			throw new Exception('HTTP header cannot be set without a key.');
		
		// add to existing value if specified
		if (!$overwrite && isset($this[$index]))
			$newval = array_merge((array) $newval, (array) $this[$index]);
		// format the value
		if (is_array($newval) && count($newval) == 1)
			$newval = preg_replace('/\n(\S)/', "\n \\1", (string) $newval[0]);
		else if (is_array($newval))
			foreach ($newval as &$value)
				$value = preg_replace('/\n(\S)/', "\n \\1", (string) $value);
		else
			$newval = preg_replace('/\n(\S)/', "\n \\1", (string) $newval);
		// set the entry
		parent::offsetSet(strtolower($index), $newval);
			
	}

	public function offsetUnset($index) {
		parent::offsetUnset(strtolower($index));
	}
}

//--------------------------------------------------------------------------
// cookies array object
//--------------------------------------------------------------------------

class HTTPCookieArray implements IteratorAggregate, ArrayAccess, Countable {
	protected $message;
	
	function __construct(HTTPMessage $message) {
		// save the message object
		$this->message = $message;
	}

	protected function getArray() {
		// parse HTTPRequest Cookie: header
		if ($this->message instanceof HTTPRequest)
			$cookies = http_parse_query(str_replace(';', '&', $this->message->getHeader('Cookie')));
		// parse HTTPResponse Set-Cookie: header
		if ($this->message instanceof HTTPResponse)
			$cookies = http_parse_query(implode('&', preg_replace('/\s*;.*$/', '',
			    (array) $this->message->getHeader('Set-Cookie'))));
		
		// return the array of cookies
		return $cookies;
	}
	
	public function offsetExists($offset) {
		// return whether the cookie exists
		$cookies = $this->getArray();
		return isset($cookies[$offset]);
	}

	public function offsetGet($offset) {
		// return the cookie
		$cookies = $this->getArray();
		return $cookies[$offset];
	}

	public function offsetSet($index, $newval, $params = '') {
		// validate the index
		if (!strlen($index))
			throw new Exception('HTTP cookie cannot be set without a key.');
		// delete any existing cookie
		$this->offsetUnset($index);	

		// add to HTTPRequest Cookie: header
		if ($this->message instanceof HTTPRequest)
			$this->message->setHeader('Cookie',
			    http_build_query(array($index => $newval) + $this->getArray(), '', ';'));
		// add to HTTPResponse Set-Cookie: header
		if ($this->message instanceof HTTPResponse)
			foreach (explode(';', http_build_query(array($index => $newval), '', ';')) as $value)
				$this->message->setHeader('Set-Cookie', $value . $params, false);
	}

	public function offsetUnset($index) {
		// check that this index exists
		if (!isset($this[$index]))
			return;

		// remove each entry
		foreach (explode(';', http_build_query(array($index => $this[$index]), '', ';')) as $item) {
			// remove from HTTPRequest Cookie: header
			if ($this->message instanceof HTTPRequest)
				$this->message->setHeader('Cookie',
				    trim(preg_replace('(;|^)\s*' . preg_quote($item) . '\s*(?=;|$)/', '',
				        (string) $this->message->getHeader('Cookie')), ';'));
			// remove from HTTPResponse Set-Cookie: header
			if ($this->message instanceof HTTPResponse)
				// remove the entry
				$this->message->setHeader('Set-Cookie',
				    preg_grep('/^\s*' . preg_quote($item) . '\s*(?=;|$)/',
				        (array) $this->message->getHeader('Set-Cookie'), PREG_GREP_INVERT));
		}
	}
	
	public function count() {
		return count($this->getArray());
	}
	
	public function getIterator() {
		return new ArrayIterator($this->getArray());
	}
}

//==========================================================================
// HTTPRequest
//--------------------------------------------------------------------------
// see: http://www.w3.org/Protocols/rfc2616/rfc2616-sec5.html#sec5
//==========================================================================

class HTTPRequest extends HTTPMessage {
	// message data
	protected $method = 'GET';
	protected $url;
	protected $version = '1.0';

	function __construct($method = null, $url = null, $version = null) {
		// create the URL object
		$this->url = new URL();

		// set variables
		if ($method !== null)
			$this->setMethod($method);
		if ($url !== null)
			$this->setURL($url);
		if ($version !== null)
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
		// cache the current request
		static $request = null;
		if ($request !== null)
			return $request;

		// create a new request object
		$request = new HTTPRequest($_SERVER['REQUEST_METHOD']);
		// set the url
		$request->setURL(new URL(array(
			'scheme' => strtolower(preg_replace('/\/.*$/', '', $_SERVER['SERVER_PROTOCOL'])) .
			    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : ''),
			'host' => $_SERVER['SERVER_NAME'],
			'port' => $_SERVER['SERVER_PORT'],
			'path' => preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']),
			'query' => $_SERVER['QUERY_STRING'],
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

#[TODO] expand this?
		// fix some browser Accept: strings
		if ($agent = $request->getUserAgentInfo()) {
			// IE 5 & IE 6
			if ($agent->browser == 'IE' && $agent->majorver >=5 && $agent->majorver <=6)
				$request->setHeader('Accept', 'text/html,text/xml,text/plain;q=0.8,image/png,image/*;q=0.9,*/*;q=0.5');
			// IE 4
			if ($agent->browser == 'IE' && $agent->majorver == 4)
				$request->setHeader('Accept', 'text/html,text/plain;q=0.8,image/png,image/*;q=0.9,*/*;q=0.5');
			// Netscape 4
			if ($agent->browser == 'Netscape' && $agent->majorver == 4)
				$request->setHeader('Accept', 'text/html,text/plain;q=0.8,image/png,image/*;q=0.9,*/*;q=0.5');
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

//==============================================================================
// HTTPResponse
//------------------------------------------------------------------------------
// see: http://www.w3.org/Protocols/rfc2616/rfc2616-sec5.html#sec6
//==============================================================================

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

//==============================================================================
// HTTPType base class
//==============================================================================

#[TODO] better protect properties and parameters?
#[TODO] support quoted-strings in parameters

abstract class HTTPType {
	// type properties
	public $type = '';
	public $params = array();

	function __construct($type, $params = array()) {
		// set the type and parameters
		$this->type = (string) $type;
		// set the parameters
		if ($params)
			foreach ((array) $params as $key => $value)
				$this->params[$key] = (string) $value;
	}

	public function serialize($withParams = true) {
		// return the serialized type
		$string = $this->type;
		if ($withParams)
			foreach ($this->params as $key => $value)
				$string .= ';' . $key . '=' . $value;
		return $string;
	}

	public function match($matchType, $strict = false) {
		// get the current class
		$class = get_class($this);
		
		// if an array is passed, iterate each type
		if (is_array($matchType)) {
			foreach ($matchType as $item)
				if ($this->match($item, $strict))
					return $item;
			return false;
		}

		// match the type
		if (!($matchType instanceof $class) ||
		    !($this->type == $matchType->type || $this->type == '*' || $matchType->type != '*'))
			return false;
		// match the parameters (when strict)
		if ($strict && $this->params != $matchType->params)
			return false;
		// match successful
		return true;
	}

	//------------------------------------------------------------------
	// creation functions
	//------------------------------------------------------------------

	protected static function __create($class, $type, $params = array()) {
		// create and return the encoding type
		return new $class($type, $params);
	}

	protected static function __parse($class, $string) {
		// parse the type
		if (!preg_match('/^\s*(?P<type>.+?)\s*(?P<params>(;[^=]+?=[^;]+?)*)$/', $string, $components))
			return false;
		// parse the parameters
		preg_match_all('/;\s*(?P<key>[^=]+?)\s*=\s*(?P<value>[^;\s]+?)/', $components['params'], $params, PREG_PATTERN_ORDER);
		$components['params'] = @array_combine($params['key'], $params['value']);

		// create the type
		return new $class($components['type'], $components['params']);
	}

	//------------------------------------------------------------------
	// sorting functions
	//------------------------------------------------------------------

	public static function findBestMatches($accepted, $available, $sort = SORT_DESC) {
		// set up the matches array to find the best match
		$matches = array(array(), array(), array(), 'type' => array());

		// iterate through the available types
		foreach ($available as $availableType) {
			// check if this is an accepted type
			if (!($acceptedType = $availableType->match($accepted)))
				continue;

			// add the match to the array
			$matches[0][] = isset($acceptedType->params['q']) ? $acceptedType->params['q'] : 1;
			$matches[1][] = count($acceptedType->params) - isset($acceptedType->params['q']);
			$matches[2][] = $acceptedType->type != '*';
			$matches['type'][] = $availableType;
		}

		// sort and return the matches
		array_multisort(
			$matches[0], $sort,
			$matches[1], $sort,
			$matches[2], $sort,
			$matches['type'], $sort
		    );
		return $matches['type'];
	}
}

//==========================================================================
// CharsetType class
//--------------------------------------------------------------------------
// see: http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.2
//==========================================================================

class CharsetType extends HTTPType {
	// class overrides
	public static function create($type, $params = array()) {
		return parent::__create('CharsetType', $type, $params);
	}
	
	public static function parse($string) {
		return parent::__parse('CharsetType', $string);
	}
}

//==========================================================================
// EncodingType class
//--------------------------------------------------------------------------
// see: http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.3
//==========================================================================

class EncodingType extends HTTPType {
	// class overrides
	public static function create($type, $params = array()) {
		return parent::__create('EncodingType', $type, $params);
	}
	
	public static function parse($string) {
		return parent::__parse('EncodingType', $string);
	}
}

//==========================================================================
// LanguageType class
//--------------------------------------------------------------------------
// see: http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
//==========================================================================

class LanguageType extends HTTPType {
	// class overrides
	public static function create($type, $params = array()) {
		return parent::__create('LanguageType', $type, $params);
	}
	
	public static function parse($string) {
		return parent::__parse('LanguageType', $string);
	}
}

//==========================================================================
// MIMEType class
//--------------------------------------------------------------------------
// see: http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.1
//==========================================================================

class MIMEType extends HTTPType {
	public $type = '';
	public $subtype = '';
	public $params = array();

	function __construct($type, $subtype, $params = null) {
		// set the type, subtype, and parameters
		$this->type = (string) $type;
		$this->subtype = (string) $subtype;
		// validate the parameters
		if ($params)
			foreach ((array) $params as $key => $value)
				$this->params[$key] = (string) $value;
	}

	public function serialize($withParams = true) {
		// return the serialized type
		$string = $this->type . '/' . $this->subtype;
		if ($withParams)
			foreach ($this->params as $key => $value)
				$string .= ';' . $key . '=' . $value;
		return $string;
	}

	public function match($matchType, $strict = false) {
		// get the current class type
		$class = get_class($this);

		// if an array is passed, iterate each type
		if (is_array($matchType)) {
			foreach ($matchType as $item)
				if ($this->match($item, $strict))
					return $item;
			return false;
		}

		// match the type and subtype
		if (!($matchType instanceof $class) ||
		    !($this->type == $matchType->type || $this->type == '*' || $matchType->type != '*') ||
		    !($this->subtype == $matchType->subtype || $this->subtype == '*' || $matchType->subtype == '*'))
			return false;
		// match the parameters (when strict)
		if ($strict && $this->params != $matchType->params)
			return false;
		// match successful
		return true;
	}

	//------------------------------------------------------------------
	// creation functions
	//------------------------------------------------------------------

	public static function create($type, $subtype, $params = array()) {
		// create and return the mime type
		return new MIMEType($type, $subtype, $params);
	}

	public static function parse($string) {
		// parse the type
		if (!preg_match('/^\s*(?P<type>.+?)\s*\/\s*(?P<subtype>.+?)\s*(?P<params>(;[^=]+?=[^;]+?)*)$/', $string, $components))
			return false;
		// parse the parameters
		preg_match_all('/;\s*(?P<key>[^=]+?)\s*=\s*(?P<value>[^;\s]+)/', $components['params'], $params, PREG_PATTERN_ORDER);
		$components['params'] = @array_combine($params['key'], $params['value']);
		
		// create the type
		return new MIMEType($components['type'], $components['subtype'], $components['params']);
	}

	//------------------------------------------------------------------
	// sorting functions
	//------------------------------------------------------------------

	public static function findBestMatches($accepted, $available, $sort = SORT_DESC) {
		// set up the matches array to find the best match
		$matches = array(array(), array(), array(), array(), array(), 'type' => array());

		// iterate through the available type
		foreach ($available as $availableType) {
			// check if this is an accepted type
			if (!($acceptedType = $availableType->match($accepted)))
				continue;

			// add the match to the array
			$matches[0][] = isset($acceptedType->params['q']) ? $acceptedType->params['q'] : 1;
			$matches[1][] = isset($availableType->params['qs']) ? $availableType->params['qs'] : 1;
			$matches[2][] = count($acceptedType->params) - isset($acceptedType->params['q']);
			$matches[3][] = $acceptedType->type != '*';
			$matches[4][] = $acceptedType->subtype != '*';
			$matches['type'][] = $availableType;
		}

		// sort and return the matches
		array_multisort(
			$matches[0], $sort,
			$matches[1], $sort,
			$matches[2], $sort,
			$matches[3], $sort,
			$matches[4], $sort,
			$matches['type'], $sort
		    );
		return $matches['type'];
	}
}

//==============================================================================
// URL class
//==============================================================================

class URL {
	protected $components = array();
	
	function __construct($components = null) {
		// set the components
		if ($components)
			$this->setComponents($components);
	}

	//----------------------------------------------------------------------
	// setters and getters
	//----------------------------------------------------------------------
	
	public function serialize() {
		// return a serialized URL
		return ($this->scheme ? $this->scheme . '://' : '') .
		    ($this->user ? urlencode($this->user) .
		        ($this->pass ? ':' . urlencode($this->pass) : '') . '@' : '') .
		    $this->host . ($this->port ? ':' . $this->port : '') . $this->path .
		    ($this->query ? '?' . $this->query : '') . ($this->fragment ? '#' . $this->fragment : '');
	}
	
	//----------------------------------------------------------------------
	// components
	//----------------------------------------------------------------------
	
	public function getComponents() {
		// return the components array
		return $this->components;
	}

	public function setComponents($components, $clear = false) {
		// clear components if specified
		if ($clear)
			$this->components = array();
		// validate and normalize components
		foreach ((array) $components as $key => $value)
			if (in_array($key, array('scheme', 'user', 'pass',
			    'host', 'port', 'path', 'query', 'fragment')))
				$this->components[$key] = ($value === null ? null : (string) $value);
	}
	
	//----------------------------------------------------------------------
	// url construction
	//----------------------------------------------------------------------
	
	static public function create($components) {
		// build a URL from components
		return new URL($components);
	}

	static public function parse($url) {
		// build a componentized url
		if (($components = @parse_url($url)) === false)
			return false;
		return new URL($components);
	}

	//----------------------------------------------------------------------
	// magic methods
	//----------------------------------------------------------------------
	
	function __get($key) {
		return isset($this->components[$key]) ? $this->components[$key] : false;
	}

	function __set($key, $value) {
		$this->setComponents(array($key => $value));
	}
	
	function __toString() {
		return $this->serialize();
	}
}

//==============================================================================
// HTTPStatusException class
//==============================================================================

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
  <title>'. $this->response->getStatusCode() . ' Error: '. $this->response->getStatusMessage() . '</title>
 </head>
 <body>
  <h1>'. $this->response->getStatusCode() . ' Error: '. $this->response->getStatusMessage() . '</h1>
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

//==============================================================================
// http functions
//==============================================================================

function http_parse_query($query, $arg_separator = null) {
	// get the arg separator
	if (strlen($arg_separator))
		$query = str_replace(array('&', $arg_separator), array('\&', '&'), $query);
	// parse the string
	parse_str($query, $data);
	return strip_magic_quotes($data);
}

function strip_magic_quotes($array, $isTopLevel = true) {
	// see: http://us2.php.net/manual/en/function.get-magic-quotes-gpc.php#49612
	$isMagic = get_magic_quotes_gpc();
	$cleanArray = array();
	foreach ((array) $array as $key => $value) {
		if (is_array($value))
			$cleanArray[$isMagic && !$isTopLevel ? stripslashes($key) : $key] =
			    strip_magic_quotes($value, false);
		else
			$cleanArray[stripslashes($key)] = ($isMagic) ?
			    stripslashes($value) : $value;
	}
	return $cleanArray;
}

?>