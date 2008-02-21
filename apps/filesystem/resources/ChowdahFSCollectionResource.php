<?php

//------------------------------------------------------------------------------
// chowdah filesystem collection resource
//------------------------------------------------------------------------------

class ChowdahFSCollectionResource implements HTTPResource, Collection {
	// internal document object
	protected $file;
	
	function __construct(ChowdahFSCollection $file) {
		// save the internal object
		$this->file = $file;
	}

	//----------------------------------------------------------------------
	// Collection functions
	//----------------------------------------------------------------------

	public function getChild($filename) {
		// get the child file (while not displaying hidden files)
		if ($filename[0] == '.' || !($child = $this->file->getChild($filename)))
			return false;	
		
		// check if there is a resource-type for the file
		if ($child && strlen($type = $child->getMetadata('resource-type'))) {
			// split $type identifier
			list ($app, $class) = explode('::', $type, 2);
		
			// load the application
			if (!Chowdah::setCurrentApplication($app))
				Chowdah::log('Non-existant application "' . $app . '" requested.');
			// load the resource
			return Chowdah::createResource($class, array($child));
		}
		// else return the child object
		return $child instanceof Collection ?
		    new ChowdahFSCollectionResource($child) :
		    new ChowdahFSDocumentResource($child);
	}
	
	public function getChildren($flag = null) {
		// create an array of children resources
		$children = array();
		foreach ($this->file->getChildren($flag) as $filename => $file)
			$children[$filename] = $this->getChild($filename);
		return array_filter($children);
	}
	
	//----------------------------------------------------------------------
	// Chowdah resource functions
	//----------------------------------------------------------------------

	public function handle(HTTPRequest $request) {
		// check if there is a directory index
		if (isset($this->file->metadata['index']) &&
		    ($child = $this->getChild($this->file->metadata['index'])))
			return $child->handle($request);

		// create the response
		$response = new HTTPResponse();
		
		// handle the request
		switch ($request->getMethod()) {
		    case 'GET':
			// check if directory listing is allowed
			if (!in_array($this->file->metadata['allow-directory-list'],
			    array('true', 'yes', '1'), true))
				throw new HTTPStatusException(403, 'Forbidden', 'Directory listing is forbidden.');
		
			// create a basic directory list
			$response->setContent(
'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
 <head>
  <title>Index of ' . $request->getURLComponents()->path . '</title>
 </head>
 <body>
  <h1>Index of ' . $request->getURLComponents()->path . '</h1>
  <table>
   <thead>
    <th>Name</th><th>Last Modified</th><th>Content Type</th><th>Size</th>
   </thead>
   <tbody>
    <tr><td colspan="3"><a href="../">Parent Directory</a></td></tr>');
			// add collections
			foreach ($this->file->getChildren(Collection::ONLY_COLLECTIONS) as $child => $file)
				$response->appendContent(
				    '   <tr><td>[DIR] <a href="' . $child . '/">' . $child . '</a></td>' .
				    '<td>' . date('F j, Y', $file->getModificationTime()) .'</td>' .
				    '<td>-</td>' .
				    '<td>-</td>' .
				    "</tr>\n");
			// add documents
			foreach ($this->file->getChildren(Collection::ONLY_DOCUMENTS) as $child => $file)
				$response->appendContent(
				    '   <tr><td>[FILE] <a href="' . $child . '">' . $child . '</a></td>' .
				    '<td>' . date('F j, Y', $file->getModificationTime()) .'</td>' .
				    '<td>' . $file->getContentType()->serialize(true) . '</td>' .
				    '<td>' . (ceil($file->getSize()  / 100) / 10) . ' KB </td>' .
				    "</tr>\n");
			// add footer
			$response->appendContent(
'   </tbody>
  </table>
  <hr>
  <p><strong>' . $request->getMethod() . '</strong> on <em>' . $request->getURL() . '</em></p>
 </body>
</html>');
			$response->setContentType(new MIMEType('text', 'html'));
			break;
		
		    default:
			return false;
		}
		
		// send response
		return $response;
	}
	
	public function getAllowedMethods() {
		return array('GET');
	}
}

?>