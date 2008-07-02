<?php

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

?>