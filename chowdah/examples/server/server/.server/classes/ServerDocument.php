<?php

//------------------------------------------------------------------------------
// server document
//------------------------------------------------------------------------------

class ServerDocument extends FSDocument implements IServerFile {
	//----------------------------------------------------------------------
	// metadata extensions
	//----------------------------------------------------------------------

	public function getMetadata($key)
	{
		if ($file = $this->getParent()->getMetadataFile(false))
			return $file->getValue($key, $this->getFilename());
		return false;
	}
	
	public function setMetadata($key, $value)
	{
		$file = $this->getParent()->getMetadataFile(true); 
		return $file->setValue($key, $value, $this->getFilename());
	}

	public function deleteMetadata($key)
	{
		if ($file = $this->getParent()->getMetadataFile(false))
			return $file->deleteValue($key, $this->getFilename());
		return false;
	}

	//----------------------------------------------------------------------
	// WriteableDocument extensions
	//----------------------------------------------------------------------
	
	public function getContentType() {
		// return this file's content type
		if ($mimetype = MIMEType::parse($this->getMetadata('content_type')))
			return $mimetype;
		return parent::getContentType();
	}

	public function setContentType(MIMEType $mimetype) {
		// set this file's content type
		return $this->setMetadata('content_type', $mimetype->serialize(true));
	}

	//----------------------------------------------------------------------
	// FSDocument override
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
			if ($file = $this->getParent()->getMetadataFile(false))
				return (array) $file->getSection($this->getFilename());
			return array();
		}
	}
}

?>