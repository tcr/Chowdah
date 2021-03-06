<?php

/**
 * Writeable Document Interface
 * @package chowdah.file
 */

interface IWriteableDocument extends IDocument {
	public function setContent($data);
	public function setContentType(MIMEType $mimetype);
}

?>