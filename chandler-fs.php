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
	public function getIndexEntry();
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
			// if specified, attempt to create the entry
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
		if ($entry = $this->getIndexEntry(false))
			$mimetype = MIMEType::parse($entry->getContentType());
		if (!$mimetype)
			$mimetype = parent::getContentType();
		return $mimetype;
	}

	public function setContentType(MIMEType $mimetype) {
		// set this file's content type
		return $this->getIndexEntry(true)->setContentType($mimetype->serialize(true));
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
		// if specified, attempt to create the index
		if ($create) {
			file_put_contents($path, '<?xml version="1.0" ?><chowdah-index />');
			return ChowdahIndex::load($path);
		}
		return false;
	}

	public function getIndexEntry($create = false) {
		// get the chowdah index entry for this collection
		if ($index = $this->getParent()->getIndex($create)) {
			// if an entry exists, return it
			if ($entry = $index->getFile($this->getFilename()))
				return $entry;
			// if specified, attempt to create the entry
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

//------------------------------------------------------------------------------
// chowdah filesystem document resource
//------------------------------------------------------------------------------

class ChowdahFSDocumentResource implements ChowdahResource, Document {
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

class ChowdahFSCollectionResource implements ChowdahResource, Collection {
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
		// get the child file
		$child = $this->file->getChild($filename);
		// check if there is an entry for the file
		if ($child && ($entry = $child->getIndexEntry(false)) && ($type = $entry->getResourceType())) {
			// attempt to load the application
			if ($resource = Chowdah::createResource($type, array($child)))
				return $resource;
			else
				Chowdah::log('Non-existant resource type "' . $type . '" requested.');
		}
		// return the child object
		return $child instanceof Collection ?
		    new ChowdahFSCollectionResource($child) :
		    new ChowdahFSDocumentResource($child);
	}
	
	public function getChildren($flag = null) {
		// apply class checking
		$class = $flag == Collection::ONLY_DOCUMENTS ? 'Document' :
		    ($flag == Collection::ONLY_COLLECTIONS ? 'Collection' : 'ChowdahResource');
		// create an array of children
		$children = array();
		foreach (new DirectoryIterator($this->file->getPath()) as $file)
			if (($child = $this->getChild($file->getFilename())) && ($child instanceof $class))
				$children[$file->getFilename()] = $child;
		ksort($children);
		return $children;
	}
	
	//----------------------------------------------------------------------
	// Chowdah resource functions
	//----------------------------------------------------------------------

#[TODO] directory list should list resources, not files!

	public function handle(HTTPRequest $request) {
		// create the response
		$response = new HTTPResponse();
		
		// handle the request
		switch ($request->getMethod()) {
		    case 'GET':
			// create a basic directory list
			$response->setContent(
'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
 <head>
  <title>Index of ' . $request->getURIComponents()->path . '</title>
 </head>
 <body>
  <h1>Index of ' . $request->getURIComponents()->path . '</h1>
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
  <p><strong>' . $request->getMethod() . '</strong> on <em>' . $request->getURI() . '</em></p>
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
	// index path 
	protected $path;
	// xml references
	protected $doc;
	protected $xpath;
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
		// xpath
		$this->xpath = new DOMXPath($this->doc);
		
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
		foreach ($this->xpath->evaluate('/chowdah-index/file') as $node)
			if ($node->getAttribute('name') == $filename)
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
		$node = $this->doc->createElement('file');
		$node->setAttribute('name', $filename);
		$this->doc->documentElement->appendChild($node);
		// return the entry
		return new ChowdahIndexFile($node);
	}
	
	public function deleteFile($filename) {
		// check that the entry exists
		if (!$this->getFile($filename))
			return false;
		// delete the entry
		foreach ($this->xpath->evaluate('/chowdah-index/file') as $node)
			if ($node->getAttribute('name') == $filename)
				$node->parentNode->removeChild($node);
	}

	public function importFile(ChowdahIndexFile $file, $overwrite = false, $filename = null) {
		// get the name of the file
		$filename = strlen($filename) ? $filename : $file->getFilename();
		// add the file entry
		if (!($newFile = $this->addFile($filename, $overwrite)))
			return false;
		
		// set the properties
		if ($resourceType = $file->getResourceType())
			$newFile->setResourceType($resourceType);
		if ($contentType = $file->getContentType())
			$newFile->setResourceType($contentType);
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

class ChowdahIndexFile {
	// xml references
	protected $node;
	protected $xpath;

	function __construct(DOMElement $node) {
		// save XML references
		$this->node = $node;
		$this->xpath = new DOMXPath($this->node->ownerDocument);
	}
	
	// filename
	
	public function getFilename() {
		return $this->node->getAttribute('name');
	}

	// resource type
	
	public function getResourceType() {
		return $this->node->hasAttribute('resource-type') ?
		    $this->node->getAttribute('resource-type') : false;
	}
	
	public function setResourceType($type) {
		// validate the type
		if (!strlen($type))
			return false;
		// set the type
		$this->node->setAttribute('resource-type', $type);
	}
	
	public function deleteResourceType() {
		// delete the resource type
		$this->node->removeAttribute('resource-type');
	}

	// content type
	
	public function getContentType() {
		return $this->node->hasAttribute('content-type') ?
		    $this->node->getAttribute('content-type') : false;
	}
	
	public function setContentType($type) {
		// validate the type
		if (!strlen($type))
			return false;
		// set the type
		$this->node->setAttribute('content-type', $type);
	}
	
	public function deleteContentType() {
		// delete the resource type
		$this->node->removeAttribute('content-type');
	}

	// metadata
	
	public function getMetadata($key) {
		// scan for a value with this key
		foreach ($this->xpath->evaluate('meta', $this->node) as $meta)
			if ($meta->getAttribute('name') == $key)
				return $this->xpath->evaluate('string(.)', $meta);
		return false;
	}
	
	public function setMetadata($key, $value) {
		// validate the key
		if (!strlen($key))
			return false;
		// remove existing metadata nodes
		$this->deleteMetadata($key);
		// add a new metadata node
		$meta = $this->node->ownerDocument->createElement('meta', $value);
		$this->node->appendChild($meta)->setAttribute('name', $key);
	}
	
	public function deleteMetadata($key) {
		// scan for a value with this key
		foreach ($this->xpath->evaluate('meta', $this->node) as $meta)
			if ($meta->getAttribute('name') == $key)
				$this->node->removeChild($meta);
	}
	
	// magic methods
	
	function __get($key) {
		switch ($key) {
		    case 'metadata':
			// create an array of metadata
			$metadata = array();
			foreach ($this->xpath->evaluate('meta[string-length(@name)]', $this->node) as $meta)
				$metadata[$meta->getAttribute('name')] = $this->xpath->evaluate('string(.)', $meta);
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

// call the request handler
$root = new ChowdahFSCollectionResource(new ChowdahFSCollection($_SERVER['DOCUMENT_ROOT']));
Chowdah::handle(HTTPRequest::getCurrent(), $root)->send();

?>