<?php

/**
 * Writeable Document Interface
 * @package File
 */

interface IWriteableDocument extends IDocument {
	public function setContent($data);
	public function setContentType(MIMEType $mimetype);
}

?>