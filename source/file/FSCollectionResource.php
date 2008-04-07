<?php 

/**
 * Filesystem Collection Resource
 * @package File
 */

class FSCollectionResource extends HTTPResourceBase implements Collection {
	//----------------------------------------------------------------------
	// construction
	//----------------------------------------------------------------------
	
	protected $file;
	
	function __construct(FSCollection $file) {
		// save the internal object
		$this->file = $file;
	}
	
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	protected $methods = array('GET', 'OPTIONS');
	
	public function GET(HTTPRequest $request) {
		// create the response
		$response = new HTTPResponse();
		
		// create a basic directory list
		$response->setContent(
'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
 <head>
  <title>Index of ' . $request->getURL()->path . '</title>
 </head>
 <body>
  <h1>Index of ' . $request->getURL()->path . '</h1>
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
		return $response;
	}

	//----------------------------------------------------------------------
	// collection
	//----------------------------------------------------------------------

	public function getChild($filename) {
		// get the child file (while not displaying hidden files)
		if ($filename[0] == '.' || !($child = $this->file->getChild($filename)))
			return false;
		
		// return the child object
		return $child instanceof Collection ?
		    new FSCollectionResource($child) :
		    new FSDocumentResource($child);
	}
}

?>