<?php

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

?>