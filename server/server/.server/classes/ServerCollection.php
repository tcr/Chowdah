<?php

//------------------------------------------------------------------------------
// server collection
//------------------------------------------------------------------------------

class ServerCollection extends FSCollection implements IServerFile {
	//----------------------------------------------------------------------
	// metadata extensions
	//----------------------------------------------------------------------

	public function getMetadataFile($create = false) {
		// cache metadata files
		static $file = null;
		if ($file)
			return $file;

		// get the metadata for this collection
		$path = $this->getPath() . '/.metadata.ini';
		// check if we need to create it
		if (!is_file($path) && !$create)
			return false;
		else if (!is_file($path))
			file_put_contents($path, '');
		
		return ($file = new INIFile($path));
	}

	public function getMetadata($key)
	{
		if ($file = $this->getMetadataFile(false))
			return $file->getValue($key);
		return false;
	}
	
	public function setMetadata($key, $value)
	{
		$file = $this->getMetadataFile(true); 
		return $file->setValue($key, $value);
	}

	public function deleteMetadata($key)
	{
		if ($file = $this->getMetadataFile(false))
			return $file->deleteValue($key);
		return false;
	}

	//----------------------------------------------------------------------
	// IWriteableCollection extensions
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
		// delete metadata entry
		if ($file = $this->getMetadataFile(false))
			$index->deleteSection($filename);
		return true;
	}

	public function move(File $file, $overwrite = false, $filename = null) {
		// get the old and new filenames
		$oldFilename = $file->getFilename();
		$newfilename = strlen($filename) ? $filename : $oldFilename;

		// check if the file has metadata
		if ($file instanceof ServerFile) {
			// get the old metadata
			if ($oldMetadataFile = $file->getParent()->getMetadataFile(false))
				$metadata = $oldMetadataFile->getSection($oldFilename);
			// get the new metadata file
			$newMetadataFile = $this->getMetadataFile((bool) $metadata);
		}
	
		// move the file to this directory
		if (!parent::move($file, $overwrite, $newfilename))
			return false;

		// import file metadata, if it exists
		if ($metadata)
			$newMetadataFile->setSection($metadata, $newfilename);
		// delete any lingering metadata
		if ($oldMetadataFile)
			$oldMetadataFile->deleteSection($oldFilename);
		return true;
	}

	public function copy(File $file, $overwrite = false, $filename = null) {
		// get the old and new filenames
		$oldFilename = $file->getFilename();
		$newfilename = strlen($filename) ? $filename : $oldFilename;

		// check if the file has metadata
		if ($file instanceof ServerFile) {
			// get the old metadata
			if ($oldMetadataFile = $file->getParent()->getMetadataFile(false))
				$metadata = $oldMetadataFile->getSection($oldFilename);
			// get the new metadata file
			$newMetadataFile = $this->getMetadataFile((bool) $metadata);
		}
	
		// move the file to this directory
		if (!parent::copy($file, $overwrite, $newfilename))
			return false;

		// import file metadata, if it exists
		if ($metadata)
			$newMetadataFile->setSection($metadata, $newfilename);
		return true;
	}

	//----------------------------------------------------------------------
	// FSCollection override
	//----------------------------------------------------------------------
	
	protected function getFileFromPath($path) {
		// try and return a file at the specified path
		try {
			if (is_file($path) && basename($path) != '.metadata.ini')
				return new ServerDocument($path);
			if (is_dir($path))
				return new ServerCollection($path);
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
			if ($file = $this->getMetadataFile(false))
				return (array) $file->getSection();
			return array();
		}
	}
}

?>