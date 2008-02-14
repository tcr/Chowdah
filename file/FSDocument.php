<?php

//==========================================================================
// FSDocument class
//==========================================================================

class FSDocument extends FSFile implements WriteableDocument {
	function __construct($path, $context = null) {
		// call parent constructor
		parent::__construct($path, $context);
		// check that the target is a document
		if (!is_file($this->path))
			throw new Exception('The file located at "' . $path . '" is not a document.');
	}
	
	// content

	public function getContent() {
		return file_get_contents($this->getPath(), false, $this->context);
	}

	public function setContent($data) {
		return file_put_contents($this->getPath(), $data, null, $this->context);
	}
	
	// content type
	
	public function getContentType() {
		// attempt to read the content type
		if (function_exists('mime_content_type'))
			return mime_content_type($this->getPath());
		else if (function_exists('finfo_open'))
			return MIMEType::parse(finfo::file($this->getPath(),
			    FILEINFO_MIME, $this->getContext()));
		else if ($mimetype = MIMEType::parse(@exec('file -bi ' . escapeshellarg($this->getPath()))))
			return $mimetype;
		else
			return new MIMEType('application', 'octet-stream');
	}
	
	public function setContentType(MIMEType $mimetype) {
		// can't set the content type
		return false;
	}

	// file size

	public function getSize() {
		return filesize($this->path);
	}
}

?>