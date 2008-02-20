<?php

/**
 * Virtual Document Class
 * @package File
 */

class VirtualDocument extends VirtualFile implements WriteableDocument {
	// file properties
	protected $content = '';
	protected $contentType = null;

	// content

	public function getContent() {
		return $this->content;
	}

	public function setContent($data) {
		return $this->content = (string) $data;
	}
	
	// content type
	
	public function getContentType() {
		// attempt to read the content type
		if ($this->contentType)
			return $this->contentType;
		else
			return new MIMEType('application', 'octet-stream');
	}
	
	public function setContentType(MIMEType $type) {
		$this->contentType = $type;
	}

	// file size

	public function getSize() {
		return strlen($this->content);
	}
}

?>