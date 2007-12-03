<?php

//##############################################################################
// Chowdah | Filesystem Request Handler
//##############################################################################
// written by Tim Ryan (tim-ryan.com)
// released under the MIT/X License
// we are not hackers!
//##############################################################################

require_once "chowdah.php";

//==============================================================================
// chowdah filesystem classes
//==============================================================================

interface ChowdahFSFile {
	// get the file's chowdah index entry
	public function getIndexEntry($create = false);
}

//------------------------------------------------------------------------------
// chowdah filesystem document
//------------------------------------------------------------------------------

class ChowdahFSDocument extends FSDocument implements ChowdahFSFile {
	//----------------------------------------------------------------------
	// Chowdah metadata extensions
	//----------------------------------------------------------------------

	public function getIndexEntry($create = false) {
		// get the chowdah index entry for this collection
		if ($index = $this->getParent()->getIndex($create)) {
			// if an entry exists, return it
			if ($entry = $index->getFile($this->getFilename()))
				return $entry;
			// else, attempt to create the entry
			if ($create)
				return $index->addFile($this->getFilename());
		}
		return false;
	}
	
	public function getMetadata($key) {
		// return this file's metadata
		if ($entry = $this->getIndexEntry(false))
			return $entry->getMetadata($key);
		return false;
	}
	
	public function setMetadata($key, $value) {
		// set the metadata entry
		return $this->getIndexEntry(true)->setMetadata($key, $value);
	}

	public function deleteMetadata($key) {
		// delete the metadata entry
		if ($this->getMetadata($key) !== false)
			return $this->getIndexEntry(true)->deleteMetadata($key);
	}

	//----------------------------------------------------------------------
	// WriteableDocument extensions
	//----------------------------------------------------------------------
	
	public function getContentType() {
		// return this file's content type
		if ($mimetype = MIMEType::parse($this->getMetadata('content-type')))
			return $mimetype;
		return parent::getContentType();
	}

	public function setContentType(MIMEType $mimetype) {
		// set this file's content type
		return $this->setMetadata('content-type', $mimetype->serialize(true));
	}

	//----------------------------------------------------------------------
	// FSDocument override
	//----------------------------------------------------------------------

	protected function getFileFromPath($path) {
		// try and return an FSFile object of the specified path
		try {
			if (is_file($path) && basename($path) != '.chowdah-index')
				return new ChowdahFSDocument($path);
			if (is_dir($path))
				return new ChowdahFSCollection($path);
		} catch (Exception $e) { }
		
		// no match found
		return false;
	}

	//----------------------------------------------------------------------
	// magic methods
	//----------------------------------------------------------------------
	
	function __get($key) {
		switch ($key) {
		    case 'metadata':
			// return an array of metadata
			if ($entry = $this->getIndexEntry(false))
				return $entry->metadata;
			return array();
		}
	}
}

//------------------------------------------------------------------------------
// chowdah filesystem collection
//------------------------------------------------------------------------------

class ChowdahFSCollection extends FSCollection implements ChowdahFSFile {
	//----------------------------------------------------------------------
	// Chowdah metadata extensions
	//----------------------------------------------------------------------

	public function getIndex($create = false) {
		// get the chowdah index for this collection
		$path = $this->getPath() . '/.chowdah-index';
		// if an index exists, return it
		if ($index = ChowdahIndex::load($path))
			return $index;
		
		// attempt to create the index
		if (!$create)
			return false;
		file_put_contents($path, '<?xml version="1.0" ?><chowdah-index />');
		return ChowdahIndex::load($path);
	}

	public function getIndexEntry($create = false) {
		// get the chowdah index entry for this collection
		if ($index = $this->getParent()->getIndex($create)) {
			// if an entry exists, return it
			if ($entry = $index->getFile($this->getFilename()))
				return $entry;
			// else, attempt to create the entry
			if ($create)
				return $index->addFile($this->getFilename());
		}
		return false;
	}

	public function getMetadata($key) {
		// return this file's metadata
		if ($entry = $this->getIndexEntry(false))
			return $entry->getMetadata($key);
		return false;
	}
	
	public function setMetadata($key, $value) {
		// set the metadata entry
		return $this->getIndexEntry(true)->setMetadata($key, $value);
	}

	public function deleteMetadata($key) {
		// delete the metadata entry
		if ($this->getMetadata($key) !== false)
			return $this->getIndexEntry(true)->deleteMetadata($key);
		return false;
	}

	//----------------------------------------------------------------------
	// WriteableCollection extensions
	//----------------------------------------------------------------------
	
	public function createChildDocument($name, $overwrite = false, $permissions = 0644) {
		// delete any existing children if they exist
		if ($overwrite)
			$this->deleteChild($name);
		// return the created child
		return parent::createChildDocument($name, $overwrite, $permissions);
	}
	
	public function createChildCollection($name, $overwrite = false, $permissions = 0755) {
		// delete any existing children if they exist
		if ($overwrite)
			$this->deleteChild($name);
		// return the created child
		return parent::createChildCollection($name, $overwrite, $permissions);
	}

	public function deleteChild($filename) {
		// remove the selected child
		if (!parent::deleteChild($filename))
			return false;
		// delete chowdah index entry
		if ($index = $this->getIndex(false))
			$index->deleteFile($filename);
		return true;
	}

	public function move(File $file, $overwrite = false, $filename = null) {
		// get the old and new filenames
		$oldFilename = $file->getFilename();
		$newfilename = strlen($filename) ? $filename : $oldFilename;

		// check if the file has chowdah metadata
		if ($file instanceof ChowdahFSFile) {
			// get the old index file and entry
			if ($oldIndex = $file->getParent()->getIndex(false))
				$entry = $oldIndex->getFile($oldFilename);
			// get the new index file
			$newIndex = $this->getIndex((bool) $entry);
		}
	
		// move the file to this directory
		if (!parent::move($file, $overwrite, $newfilename))
			return false;

		// move file metadata, if it exists
		if ($entry) {
			// import the new entry
			$newIndex->importFile($entry, $overwrite, $newFilename);
			// delete the old entry
			$oldIndex->deleteFile($oldFilename);
		} else if ($newIndex) {
			// delete lingering metadata, if it exists
			$newIndex->deleteFile($newFilename);
		}
		return true;
	}

	public function copy(File $file, $overwrite = false, $filename = null) {
		// get the old and new filenames
		$oldFilename = $file->getFilename();
		$newfilename = strlen($filename) ? $filename : $oldFilename;

		// check if the file has chowdah metadata
		if ($file instanceof ChowdahFSFile) {
			// get the old chowdah index
			if ($oldIndex = $file->getParent()->getIndex(false))
				$entry = $oldIndex->getFile($oldFilename);
			// get the new chowdah index
			$newIndex = $this->getIndex((bool) $entry);
		}

		// copy the file to this directory
		if (!parent::copy($file, $overwrite, $newFilename))
			return false;

		// copy file metadata, if it exists
		if ($entry) {
			// import the new entry
			$newIndex->importFile($entry, $overwrite, $newFilename);
			// delete the old entry
			$oldIndex->deleteFile($oldFilename);
		} else if ($newIndex) {
			// delete lingering metadata, if it exists
			$newIndex->deleteFile($newFilename);
		}
		return true;
	}

	//----------------------------------------------------------------------
	// FSCollection override
	//----------------------------------------------------------------------
	
	protected function getFileFromPath($path) {
		// try and return an FSFile object of the specified path
		try {
			if (is_file($path) && basename($path) != '.chowdah-index')
				return new ChowdahFSDocument($path);
			if (is_dir($path))
				return new ChowdahFSCollection($path);
		} catch (Exception $e) { }
		
		// no match found
		return false;
	}

	//----------------------------------------------------------------------
	// magic methods
	//----------------------------------------------------------------------
	
	function __get($key) {
		switch ($key) {
		    case 'metadata':
			// return an array of metadata
			if ($entry = $this->getIndexEntry(false))
				return $entry->metadata;
			return array();
		}
	}
}

//==============================================================================
// chowdah filesystem resources
//==============================================================================

#[TODO] have independent FSDocumentResource class?

//------------------------------------------------------------------------------
// chowdah filesystem document resource
//------------------------------------------------------------------------------

class ChowdahFSDocumentResource implements HTTPResource, Document {
	// internal document object
	protected $file;
	
	function __construct(ChowdahFSDocument $file) {
		// save the internal object
		$this->file = $file;
	}

	//----------------------------------------------------------------------
	// Document functions
	//----------------------------------------------------------------------
	
	public function getContent() {
		return $this->file->getContent();
	}
		
	public function getContentType() {
		return $this->file->getContentType();
	}	
	
	public function getSize() {
		return $this->file->getSize();
	}
	
	//----------------------------------------------------------------------
	// Chowdah resource functions
	//----------------------------------------------------------------------
	
	public function handle(HTTPRequest $request) {
		// create the response
		$response = new HTTPResponse();
		
		// handle the request
		switch ($request->getMethod()) {
		    case 'GET':
			// display the file
			$response->setContentAsDocument($this);
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

//==============================================================================
// Chowdah Index
//==============================================================================

class ChowdahIndex {
	// index properties
	protected $path;
	protected $doc;
	// content cache
	protected $content;
	
	function __construct($path) {
		// set this path
		if (!$path || ($this->path = @realpath($path)) === false)
			throw new Exception('Chowdah Index at "' . $path . '" does not exist.');

		// load the document
		$this->doc = new DOMDocument();
		$this->doc->preserveWhiteSpace = false;
		$this->doc->formatOutput = true;
		$this->doc->load($this->path);
		
		// save a cache of the file content
		$this->content = $this->doc->saveXML();
	}

	// chowdah index cache
	
	static $cache = array();

	static function load($path) {
		// normalize the path
		$path = @realpath($path);
		// try to load a cached index file
		if (ChowdahIndex::$cache[$path])
			return ChowdahIndex::$cache[$path];
		// otherwise, construct and cache the index object
		try {
			return ChowdahIndex::$cache[$path] = new ChowdahIndex($path);
		} catch (Exception $e) {
			return false;
		}
	}
	
	// path

	public function getPath() {
		// return this index's path
		return $this->path;
	}
	
	// files

	public function getFile($filename) {
		// get the requested entry
		$xpath = new DOMXPath($this->doc);
		foreach ($xpath->evaluate('/chowdah-index/file') as $node)
			if ($xpath->evaluate('string(.)', $node) == $filename)
				return new ChowdahIndexFile($node);
	}
	
	public function addFile($filename, $overwrite = false) {
		// check if node should be overwritten
		if ($this->getEntry($filename))
			if ($overwrite)
				$this->deleteFile($filename);
			else
				return false;
		
		// create the file entry
		$node = $this->doc->createElement('file', $filename);
		$this->doc->documentElement->appendChild($node);
		// return the entry
		return new ChowdahIndexFile($node);
	}
	
	public function deleteFile($filename) {
		// check that the entry exists
		if (!$this->getFile($filename))
			return false;
		// delete the entry
		$xpath = new DOMXPath($this->doc);
		foreach ($xpath->evaluate('/chowdah-index/file') as $node)
			if ($xpath->evaluate('string(.)', $node) == $filename)
				$node->parentNode->removeChild($node);
		return true;
	}

	public function importFile(ChowdahIndexFile $file, $overwrite = false, $filename = null) {
		// get the name of the file
		$filename = strlen($filename) ? $filename : $file->getFilename();
		
		// add the file entry
		if (!($newFile = $this->addFile($filename, $overwrite)))
			return false;
		// set the metadata
		foreach ($file->metadata as $key => $value)
			$newFile->setMetadata($key, $value);
		// return the new file
		return $file;
	}

	// chowdah index saving
	
	public function save() {
		// check that the file has been modified
		$content = $this->doc->saveXML();
		if ($content == $this->content)
			return false;
		// save the content and cache it
		file_put_contents($this->path, $content);
		$this->content = $content;
		return true;
	}
	
	public static function saveAll() {
		// save all currently loaded index files
		foreach (ChowdahIndex::$cache as $index)
			$index->save();
	}
}

// save all open index files at close
register_shutdown_function(array('ChowdahIndex', 'saveAll'));

//------------------------------------------------------------------------------
// Chowdah Index File
//------------------------------------------------------------------------------

#[TODO] advanced checking for invalid keys

class ChowdahIndexFile {
	// properties
	protected $node;
	protected $filename = '';

	function __construct(DOMElement $node) {
		// save DOM node
		$this->node = $node;
		// save the filename
		$xpath = new DOMXPath($this->node->ownerDocument);
		$this->filename = $xpath->evaluate('string(.)', $node);
	}
	
	// filename
	
	public function getFilename() {
		return $this->filename;
	}

	// metadata
	
	public function getMetadata($key) {
		// get the metadata value
		return $this->node->hasAttribute($key) ? $this->node->getAttribute($key) : false;
	}
	
	public function setMetadata($key, $value) {
		// validate the key
		if (!strlen($key))
			return false;
		// set a new metadata value
		return $this->node->setAttribute($key, $value);
	}
	
	public function deleteMetadata($key) {
		// delete the metadata value
		return $this->node->removeAttribute($key);
	}
	
	// magic methods
	
	function __get($key) {
		switch ($key) {
		    case 'metadata':
			// create an array of metadata
			$metadata = array();
			foreach ($this->node->attributes as $attr)
				$metadata[$attr->name] = $attr->value;
			return $metadata;
		}
	}
}

//==============================================================================
// request entry
//==============================================================================

#[TODO] creating resource from DOCUMENT_ROOT could throw exception, so...

// initialize Chowdah
Chowdah::init();

// get the document root
$root = new ChowdahFSCollection($_SERVER['DOCUMENT_ROOT']);
$filename = $root->getFilename();
$root = new ChowdahFSCollectionResource($root->getParent());
$root = $root->getChild($filename);

// call the request handler
Chowdah::handle(HTTPRequest::getCurrent(), $root)->send();

?>