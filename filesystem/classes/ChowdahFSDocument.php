<?php

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

?>