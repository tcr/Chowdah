<?php

/**
 * HTTP Message Base Class
 * @package chowdah.http
 */

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

#[TODO] make getDecodedContent a parameter option?
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
		
		// clear parsed content cache
		$this->parsedContentCache = null;
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
		if ($this->getContent() === null)
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
	
	protected $parsedContentCache = null;

	public function getParsedContent() {
		// if this content was cached, return that
		if ($this->parsedContentCache)
			return $this->parsedContentCache;
		
		// set error handler to check for bad requests
		set_error_handler(array('HTTPMessage', 'parsedContentErrorHandler'), error_reporting());
		
		// return a parsed representation of the content (based on MIME type)
		$parsedContent = $this->getContent();
		switch ($this->getContentType()->serialize(false))
		{
			case 'application/x-www-form-urlencoded':
				// parse the content
				$parsedContent = http_parse_query($this->getContent());
				break;
				
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

				$parsedContent = $data;
				break;

			case 'text/xml':
			case 'application/xml':
			case 'application/xhtml+xml':
				$parsedContent = new DOMDocument();
				$parsedContent->loadXML($this->getContent());
				break;

			case 'text/html':
				$parsedContent = new DOMDocument();
				$content = $this->getContent();
				if (function_exists('mb_convert_encoding'))
					$content = mb_convert_encoding($content, 'HTML-ENTITIES', mb_detect_encoding($content)); 
				else 
					$content = '<meta http-equiv="content-type" content="text/html; charset=utf-8">' . $content;
				$parsedContent->loadHTML($content);
				break;
		}
		
		// restore Chowdah error handler
		restore_error_handler();	
		// cache and return the content
		return ($this->parsedContentCache = $parsedContent);
	}
	
	public static function parsedContentErrorHandler($errno, $errstr, $errfile, $errline)
	{
		// restore Chowdah error handler
		restore_error_handler();
		
		// throw a HTTP 400 Bad Request exception
		if (error_reporting())
			throw new HTTPStatusException(HTTPStatus::BAD_REQUEST, 'Bad Request', 'The submitted content body was malformed.');
	}

	public function setParsedContent($data, $type = null) {
#[TODO] should this be based on data type?
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

#[TODO] MultipartFormData class!
			case 'multipart/form-data':
				// reconstruct from submitted data
				$this->setContent('');
				// get the boundary key, or generate one
				$boundary = isset($type->params['boundary']) ? $type->params['boundary'] :
				    '-----------------------=_' . mt_rand();
				    
				// iterate the form data
				foreach ((array) $data as $name => $entry) {
					// check the type of entry
					if ($entry instanceof IDocument) {
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
				if ($data instanceof SimpleXMLElement)
					$data = dom_import_simplexml($data)->ownerDocument;
				if ($data instanceof DOMDocument)
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
		
		// save data in cache
		return ($this->parsedContentCache = $data);
	}

	//----------------------------------------------------------------------
	// content as document
	//----------------------------------------------------------------------
	
	public function getContentAsDocument() {
		// get file path
		$path = ($this instanceof HTTPRequest ? basename($this->getURL()->path) : null);
		// create a virtual document of the entity
		$document = new VirtualDocument($path);
		$document->setContentType($this->getContentType());
		$document->setContent($this->getDecodedContent());
#[TODO] add last-modified time reading!
		return $document;
	}

	public function setContentAsDocument(IDocument $document) {
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

//==============================================================================
// http functions
//==============================================================================

function http_parse_query($query, $arg_separator = null) {
	// get the arg separator
	if (strlen($arg_separator))
		$query = str_replace(array('&', $arg_separator), array('\&', '&'), $query);
	// parse the string
	parse_str($query, $data);
	// parameters without equal signs are set to 'true'
	foreach (explode('&', $query) as $value)
		if (!strpos($value, '='))
			$data[$value] = true;
	// strip quotes
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