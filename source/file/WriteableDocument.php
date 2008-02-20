<?php

/**
 * Writeable Document Interface
 * @package File
 */

interface WriteableDocument extends Document {
	public function setContent($data);
	public function setContentType(MIMEType $mimetype);
}

?>