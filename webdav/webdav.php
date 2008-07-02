<?php

#[TODO] add MTime
#[TODO] add auto MIMEType detection, and implicit application/octet-stream as NULL
#[TODO] find out what GET on a collection yields
#[TODO] figure out what to make WebDAV—abstract, class, protected, etc...

// WebDAVHandler
// Called via an extended class via parent::METHOD($resource), where METHOD is the
// HTTP method to handle, and $resource is a ResourceObject (virtual or otherwise)
// to display

class WebDAVHandler extends RequestHandler {
	function __construct(ResourceObject $resource, HTTPRequest $request, HTTPResponse $response) {
		// call parent construct function
		parent::__construct($resource, $request, $response);

		// prevent warning in litmus check 'delete_fragment'
		if (strpos($request->uri->get(), '#'))
			throw new HTTPStatusException(400);

		#[TODO] check authorization
		#[TODO] also, no auth check on OPTIONS on '/', for silly reasons
		
		// check If: header
		if (!WebDAV::checkIfHeaderConditions($this->request, $this->response))
			throw HTTPStatusException(412);
	}

#[TODO] use self object?
	protected function GET(ResourceObject $resource) {
		// set the last modified time	 
#[TODO]		if ($resource->getMTime())
#[TODO]			$this->response->headers->set('Last-modified', gmdate('D, d M Y H:i:s ', $resource->getMTime()) . 'GMT');
#[TODO]		if ($resource->getCTime())
#[TODO]			$this->response->headers->set('Date', gmdate('D, d M Y H:i:s ', $resource->getCTime()) . 'GMT');


#[TODO] handle file streams
		// get ranges
#		$ranges = WebDAV::getRanges($this->request, $this->response);

		// set the content of the response
		if ($resource instanceof ResourceDocument) {
			$this->response->content->set($resource->getContents());
			$this->response->content->setType($resource->getMIMEType());
			$this->response->content->encodeOnSubmit(true);
		}
	}

#[TODO] massive optimization
	protected function PROPFIND(ResourceObject $resource, $namesCallback = null, $callback = null) {
		// parse prop request
		if (($propFilter = WebDAV::parsePROPFIND($this->request->content->get())) === false)
			throw new HTTPStatusException(400);
		// get the depth header (default is 'infinity')
		$depth = in_array($this->request->headers['Depth'], array('0', '1')) ? (int) $this->request->headers['Depth'] : null;

		// generate the reply header
		$this->response->status->set(207, 'Multi-Status');
		$this->response->content->setType(MIMEType::create('text', 'xml', array('charset' => 'utf-8')));

		// generate the response payload
		$doc = new DOMDocument('1.0', 'utf-8');
		$doc->appendChild($doc->createElementNS('DAV:', 'D:multistatus'));
		
		// get the base href
		$href = $this->request->uri->get();
		// iterate through the properties
		WebDAV::createPROPFINDResponse($resource, $doc->documentElement, $href, $propFilter, $depth, $namesCallback, $callback);

		// output the response
		$doc->formatOutput = true;
		$this->response->content->set($doc->saveXML());
		$this->response->content->encodeOnSubmit(true);
	}

#[TODO] instead of $new, maybe $childName?
	protected function PUT(ResourceObject $resource, $new = false) {
		// check lock status
#[TODO] check lock status!
#		if (!$this->_check_lock_status($this->path))
#			throw new HTTPStatusException(423, 'Locked');

		// for now, we don't support multipart requests
#[TODO] support multipart
		if ($this->request->content->getType()->type == 'multipart')
			throw new HTTPStatusException(501, 'Not Implemented',
			    'The service does not support multipart PUT requests');

		// if any Content-* headers are not understood, return a 501 response (RFC 2616 9.6)
		foreach ($this->request->headers as $header => $value) {
			// check that this is a Content-* header
			if (strncmp($header, 'content-', 8))
				continue;
			
			// check each header
			switch ($header) {
				case 'content-encoding':	 // RFC 2616 14.11
					// attempt to decode content; otherwise, fail
					if ($this->request->content->decode() === false)
						throw new HTTPStatusException(501, 'Not Implemented', 'The service does not support "' .
						    $this->request->content->getEncoding()->seralize(false) . '" encoding.');
					break;
				
				case 'content-range':		// RFC 2616 14.16
					// check that the byte range is supported
					if (($range = $this->request->content->getRange()) === false) {
						// content range is invalid, return entire entity
#[TODO] actually return the entire entity and a 200 status code
						throw new HTTPStatusException(416);
					}
#[TODO] make sure the implementation supports partial PUT
# this has to be done in advance to avoid data being overwritten
# on implementations that do not support this ...
					break;

				case 'content-md5':		// RFC 2616 14.15
#[TODO] actually support this?
					// MD5 checking is not supported
					throw new HTTPStatusException(501, 'Not Implemented',
					    'The service does not support content MD5 checksum verification.');
					break;

				case 'content-location':	// RFC 2616 14.14
					// meaning of this in PUT requests is undefined, so we can ignore it
				case 'content-length':
				case 'content-language':	// RFC 2616 14.12
#[TODO] actually support content type
				case 'content-type':
					// these headers are supported
					break;

				default: 
					// Content header unsupported
					throw new HTTPStatusException(501, 'Not Implemented',
					    'The service does not support the content header "' . $header . '".');
					return;
			}
		}

#[TODO] setContents, appendContents?
		// check if the resource needs to be created
		if ($new) {
			// resource does not yet exist; attempt to create it as a child of the parent
#[TODO] figure out url encoding, decoding, spaces issues
			$resourceName = urldecode(preg_replace('/.*\//', '', $this->request->uri->get()));
			try {
				if (!($resource = $resource->createChildDocument($resourceName, false)))
					throw new HTTPStatusException(403, 'Forbidden');
			} catch (Exception $e) {
				// we weren't allowed to create the resource
				throw new HTTPStatusException(403, 'Forbidden');
			}
			// set the response code
			$this->response->status->set(201);
		} else {
			// set the response code
			$this->response->status->set(204);
		}
		
		// set the resource content
		if ($range === null) {
			// overwrite all content
			$resource->setContents($this->request->content->get());
		} else {
#[TODO] multipart support is missing (see above)
			// retrieve the entities
			$entity = $resource->getContents();
			$partialEntity = $this->request->content->get();
			// verify range start is not longer than entity
			if ($range['start'] > strlen($entity))
				throw new HTTPStatusException(416);
			
			// compile the new entity
			$length = $range['end'] - $range['start'] + 1;
			$entity = substr($entity, 0, $range['start']) .
			    substr($partialEntity, 0, $length) .
			    substr($entity, $range['end'] + 1);
			// update the resource
			$resource->setContents($entity);
		}
	}

	protected function DELETE(ResourceObject $resource) {
		// fail if Depth header sent (RFC 2518 9.2)
		if ($this->request->headers['Depth'] != null &&
		    $this->request->headers['Depth'] != 'infinity')
			throw new HTTPStatusException(400, 'Bad Request');

#[TODO]		// check lock status
#		if (!$this->_check_lock_status($this->path))
#			throw new HTTPStatusException(423, 'Locked');

		// delete the resource
		if (!$resource->getParent()->deleteChild(basename($resource->getPath())))
			throw new HTTPStatusException(424, 'Failed Dependency');
		// return no content
		$this->response->status->set(204);
	}

	protected function MKCOL(ResourceObject $resource) {
		// body parsing not supported
		if (strlen($this->request->content->get()))
			throw new HTTPStatusException(415);
	
		// create the new collection as a child of the parent
		$resourceName = preg_replace('/.*\/(?!$)|\/$/', '', $this->request->uri->get());
		try {
			if (!($resource = $resource->createChildCollection($resourceName, false)))
				throw new HTTPStatusException(403, 'Forbidden');
		} catch (Exception $e) {
			// we weren't allowed to create the resource
			throw new HTTPStatusException(403, 'Forbidden');
		}
		
		// return a status of 201 Created
		$this->response->status->set(201);
	}

	public function OPTIONS($allow) {
		// Microsoft clients default to Frontpage protocol, so tell them to use WebDAV
		$this->response->headers['MS-Author-Via'] = 'DAV';
	
		// get DAV compatibility; class 1 is supported
		$dav = array(1);
		// class 2 requires locking to be supported
		if (in_array('LOCK', $allow))
			$dav[] = 2;
		
		// return the headers
		$this->response->status->set(200);
		$this->response->headers['DAV'] = implode(',', $dav);
		$this->response->headers['Allow'] = implode(',', $allow);
	}

	public function MOVE(ResourceObject $resource) {
		// get the server depth
		$depth = $this->request->headers['Depth'] !== null ? 'infinity' : $this->request->headers['Depth'];
		
		// get the destination
		$url = parse_url($this->request->headers['Destination']);
		$path = urldecode($url['path']);
		$host = $url['host'];
		if (isset($url['port']) && $url['port'] != 80)
			$host .= ':' . $url['port'];
		
		// get the host header
		$hostHeader = preg_replace('/:80$/', '', $this->request->headers['Host']);
		
		// check whether the destination is on this host or not
#[TODO] ensure no file is moved above base folder! supply base folder?
		if ($host == $hostHeader) {
			// the destination is on this server
#[TODO] we can't use filenames!
			$dest = $_SERVER['DOCUMENT_ROOT'] . $path;
#[TODO]			// check lock status
#			if (!$this->_check_lock_status($this->path))
#				throw new HTTPStatusException(423, 'Locked');
		} else {
			// the destination is on another server
			$destURI = $this->request->headers['Destination'];
		}
		
		// get overwrite flag
		$overwrite = ($this->request->headers['Overwrite'] != 'F');
	
#[TODO]		// body parsing not supported
		if (strlen($this->request->content->get()))
			throw new HTTPStatusException(415);
		
#[TODO]		// no copying to other servers yet
		if (isset($destURI))
			throw new HTTPStatusException(502);
		  
#[TODO]		// property updates are broken

		// check if the destination file exists or not
		$new = !file_exists($dest);
		$existing_col = false;

		// if the destination is a folder, overwrite, or make child
		if (!$new && is_dir($dest)) {
			// prevent overwrite
			if (!$overwrite)
				throw new HTTPStatusException(412);
			// add the filename to the destination
			$dest .= basename($resource->getPath());
			// 
				if (file_exists($dest)) {
					$options["dest"] .= basename($source);
				} else {
					$new = true;
					$existing_col = true;
				}
		}

		if (!$new) {
			if ($options["overwrite"]) {
				$stat = $this->DELETE(array("path" => $options["dest"]));
				if (($stat{0} != "2") && (substr($stat, 0, 3) != "404")) {
					return $stat; 
				}
			} else {				
				return "412 precondition failed";
			}
		}

		if (is_dir($source) && ($options["depth"] != "infinity")) {
			// RFC 2518 Section 9.2, last paragraph
			return "400 Bad request";
		}

		if ($del) {
			if (!rename($source, $dest)) {
				return "500 Internal server error";
			}
			$destpath = $this->_unslashify($options["dest"]);
			if (is_dir($source)) {
				$query = "UPDATE properties 
							 SET path = REPLACE(path, '".$options["path"]."', '".$destpath."') 
						   WHERE path LIKE '".$this->_slashify($options["path"])."%'";
				mysql_query($query);
			}

			$query = "UPDATE properties 
						 SET path = '".$destpath."'
					   WHERE path = '".$options["path"]."'";
			mysql_query($query);
		}

		return ($new && !$existing_col) ? "201 Created" : "204 No Content";
	}

    function http_MOVE() 
    {
        if ($this->_check_lock_status($this->path)) {
            // destination lock status is always checked by the helper method
            $this->_copymove("move");
        } else {
            $this->http_status("423 Locked");
        }
    }
}

// WebDAV-specific functions

class WebDAV {
	static public function getRanges(HTTPRequest $request, HTTPResponse $response) {
		// process Range: header if present
		// only standard "bytes" range specifications is supported for now
		if ($request->headers->get('Range') && preg_match('/bytes\s*=\s*(.+)/', $request->headers->get('Range'), $matches)) {
			// return a list of ranges
			$ranges = array();
			foreach (explode(',', $matches[1]) as $range) {
				// ranges are either from-to pairs or just end positions
				list ($start, $end) = explode('-', $range);
				$ranges[] = $start === '' ?
					array('last' => $end) :
					array('start' => $start, 'end' => $end);
			}
			return $ranges;
		}

		// no ranges header was found
		return false;
	}

	static public function checkIfHeaderConditions(HTTPRequest $request, HTTPResponse $response) {
		// check for an If: header
		if (!$request->headers->get('If'))
			return true;

		// parse the If: header
		foreach (WebDAV::ifHeaderParser($request->headers->get('If')) as $uri => $conditions) {
			// use current uri if condition is blank
			if ($uri == '')
				$uri = $request->uri->get();

			// check and match all conditions
			$state = true;
			foreach ($conditions as $condition) {
				// lock tokens may be free form (RFC2518 6.3)
				// but if opaquelocktokens are used (RFC2518 6.4)
				// we have to check the format (litmus tests this)
				if (!strncmp($condition, "<opaquelocktoken:", strlen("<opaquelocktoken")) &&
					!preg_match('/^<opaquelocktoken:[[:xdigit:]]{8}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{12}>$/', $condition))
					throw new HTTPStatusException(423);
				// check the URI condition
				if (!WebDAV::checkURICondition($uri, $condition))
					$state = false;
			}

			// if any match is found, return true
			return true;
		}

		// no match was found, throw 412 Precondition Failed
		throw new HTTPStatusException(412);
	}

	static public function parsePROPFIND($payload) {
		// verify the document
		if (!strlen((string) $payload))
			return 'all';
		else if (!($doc = DOMDocument::loadXML($payload)))
			return false;
		// create and XPath context
		$xpath = new DOMXPath($doc);
		$xpath->registerNamespace('D', 'DAV:');
		
		// check if returning all properties
		if ($xpath->evaluate('boolean(/D:propfind/D:allprop)'))
			return 'all';
		// check if returning property names	
		if ($xpath->evaluate('boolean(/D:propfind/D:propname)'))
			return 'names';
		// check if there's a list of properties
		$propNodes = $xpath->evaluate('/D:propfind/D:prop/*');
		if ($propNodes->length) {
			$props = array();
			foreach ($propNodes as $propNode)
				$props[] = $propNode->namespaceURI . ' ' . $propNode->localName;
			return $props;
		}
		// default
		return 'all';
	}

	static public function getResourceDAVPropertyNames(ResourceObject $resource) {
		// get the available property names for a resource
		$props = array('DAV: displayname', 'DAV: creationdate', 'DAV: getlastmodified',
		    'DAV: resourcetype', 'DAV: supportedlock', 'DAV: lockdiscovery');
		if ($resource instanceof ResourceDocument)
			$props = array_merge($props, array('DAV: getcontenttype', 'DAV: getcontentlength'));
		return $props;
	}

	static public function getResourceDAVProperties(ResourceObject $resource, $parent, $propFilter) {
		// get the owner document
		$doc = $parent->ownerDocument;

		// unavailable properties array
		$unavailableProps = array();
		// add requested properties
		foreach ($propFilter as $propName) {
			switch ($propName) {
				case 'DAV: displayname':
					// get display name
					$node = $parent->appendChild($doc->createElementNS('DAV:', 'displayname',
					    $resource->getFilename()));
					break;

				case 'DAV: creationdate':
					// get creation date
					$date = $resource->getCTime();
					$node = $parent->appendChild($doc->createElementNS('DAV:', 'creationdate', gmdate('Y-m-d\TH:i:s\Z', $date)));
					$node->setAttributeNS(WebDAV::MS_DT_NS, 'ns0:dt', 'dateTime.tz');
					break;

				case 'DAV: getlastmodified':
					// get last modified time
					$date = $resource->getMTime();
					$node = $parent->appendChild($doc->createElementNS('DAV:', 'getlastmodified', gmdate('D, d M Y H:i:s ', $date)));
					$node->setAttributeNS(WebDAV::MS_DT_NS, 'ns0:dt', 'dateTime.rfc1123');
					break;

				case 'DAV: resourcetype':
					// get the resource type
					$node = $parent->appendChild($doc->createElementNS('DAV:', 'resourcetype'));
					if ($resource instanceof ResourceCollection)
						$node->appendChild($doc->createElementNS('DAV:', 'collection'));
					break;

				case 'DAV: getcontenttype':
					// get the content type
					if ($resource instanceof ResourceDocument)
						$node = $parent->appendChild($doc->createElementNS('DAV:', 'getcontentlength', $resource->getMIMEType()->serialize()));
					break;

				case 'DAV: getcontentlength':
					// get the content length
					if ($resource instanceof ResourceDocument)
						$node = $parent->appendChild($doc->createElementNS('DAV:', 'getcontentlength', strlen($resource->getContents())));
					break;
					
				case 'DAV: supportedlock':
#[TODO]					// get supported lock
#[TODO]					$node = $parent->appendChild($doc->createElementNS('DAV:', 'supportedlock', $value));
					break;

				case 'DAV: lockdiscovery':
#[TODO]					// handle lockdiscovery
#[TODO]					WebDAV::lockdiscovery($resource);
					break;

				default:
					// property unavailable
					$unavailableProps[] = $propName;
					break;
			}
		}

		// return all unavailable properties
		return $unavailableProps;
	}

#[TODO] support forbidden properties
	static public function createPROPFINDResponse(ResourceObject $resource, $parent, $href, $propFilter, $depth, $namesCallback, $callback = null) {
		// get owner document
		$doc = $parent->ownerDocument;
		// create the response node
		$response = $parent->appendChild($doc->createElementNS('DAV:', 'response'));
		$response->appendChild($doc->createElementNS('DAV:', 'href', $href));
		// create propstat node for available properties
		$propstat = $response->appendChild($doc->createElementNS('DAV:', 'propstat'));
		$prop = $propstat->appendChild($doc->createElementNS('DAV:', 'prop'));

		// handle request
		if ($propFilter == 'names') {
			// get a list of all custom and DAV properties
			$propFilter = WebDAV::getResourceDAVPropertyNames($resource);
			if ($namesCallback)
				$propFilter = array_unique(array_merge($propFilter,
				    call_user_func($namesCallback, $resource)));

			// generate the list
			foreach ($propFilter as $propName) {
				// get namespace and property name
				$ns = preg_replace('/ .*$/', '', $propName);
				$name = preg_replace('/^.* /', '', $propName);
				// add empty element
				$prop->appendChild($doc->createElementNS($ns, $name));
			}
		} else {
			// filtered property list
			$props = $propFilter;
			// if all properties are requested, filter for all available properties
			if (!is_array($props)) {
				$props = WebDAV::getResourceDAVPropertyNames($resource);
				if ($namesCallback)
					$props = array_unique(array_merge($props,
					    call_user_func($namesCallback, $resource)));
			}

			// call any callback for custom properties, and get unavailable properties
			if (is_array($callback))
				$props = call_user_func($callback, $resource, $prop, $props);
			// call the DAV property handler, and get unavailable properties
			$props = WebDAV::getResourceDAVProperties($resource, $prop, $props);

			// add property status
			$propstat->appendChild($doc->createElementNS('DAV:', 'status', 'HTTP/1.1 200 OK'));

			// iterate through unavailable properties
			if (count($props)) {
				// create propstat node for unavailable properties
				$propstat = $response->appendChild($doc->createElementNS('DAV:', 'propstat'));
				$prop = $propstat->appendChild($doc->createElementNS('DAV:', 'prop'));

				// respond with all unavailable properties as empty nodes
				foreach ($props as $propName) {
					// get namespace and property name
					$ns = preg_replace('/ .*$/', '', $propName);
					$name = preg_replace('/^.* /', '', $propName);
					// add empty element
					$prop->appendChild($doc->createElementNS($ns, $name));
				}

				// add propstat status
				$propstat->appendChild($doc->createElementNS('DAV:', 'status', 'HTTP/1.1 404 Not Found'));
			}
		}

		// if Depth header was set, add child resource's response
		if ($depth !== 0 && $resource instanceof ResourceCollection) {
			// iterate through children
			foreach ($resource->getChildren() as $child)
				WebDAV::createPROPFINDResponse($child, $parent,
				    $href . $child->getFilename() . ($child instanceof ResourceCollection ? '/' : ''),
				    $propFilter, $depth === null ? null : 0, $namesCallback, $callback);
		}
	}

	// Microsoft clients need this special namespace for date and time values
	const MS_DT_NS = 'urn:uuid:c2f41010-65b3-11d1-a29f-00aa00c14882/';
}

?>