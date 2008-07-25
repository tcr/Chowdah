<?php

/**
 * Filesystem Document Class
 * @package chowdah.file
 */

class FSDocument extends FSFile implements IWriteableDocument {
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
		if (function_exists('mime_content_type') && mime_content_type($this->getPath()))
			return MIMEType::parse(mime_content_type($this->getPath()));
		else if (function_exists('finfo_open'))
			return MIMEType::parse(finfo::file($this->getPath(),
			    FILEINFO_MIME, $this->getContext()));
		else if (($file = Chowdah::getConfigSetting('mime_types')) && is_file($file))
		{
			$ext = array_pop(explode('.', $this->getFilename()));
			if (preg_match('/^([^#]\S+)[\t ]+.*\b' . $ext . '\b.*$/m', file_get_contents($file), $m))
				return MIMEType::parse($m[1]);
		}
		if ($mimetype = MIMEType::parse(@exec('file -bi ' . escapeshellarg($this->getPath()))))
			return $mimetype;
		else
			return new MIMEType('application', 'octet-stream');
	}
	
	public function setContentType(MIMEType $mimetype) {
		// cannot set the content type on filesystem
		return false;
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