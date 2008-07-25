<?php

/**
 * Virtual Document Class
 * @package chowdah.file
 */

class VirtualDocument extends VirtualFile implements IWriteableDocument, IHTTPConvertableDocument {
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
	
	// http message
	
	public function getHTTPMessageContent(HTTPMessage $message))
	{
		// load entity content
		$this->setContentType($message->getContentType());
		$this->setContent($message->getDecodedContent());
		// set file path
		if ($message instanceof HTTPRequest)
			$this->path = $message->getURL()->path;
		// last modified header
		$message->setHeader('Last-Modified', date(DATE_RFC2822, $this->getModificationTime()));
	}

	public function setHTTPMessageContent(HTTPMessage $message)
	{
		// save this document as message content
		$message->setContentType($this->getContentType());
		$message->setContent($this->getContent());
		// last modified header
		$message->setHeader('Last-Modified', date(DATE_RFC2822, $this->getModificationTime()));
	}
}

?>