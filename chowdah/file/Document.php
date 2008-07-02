<?php

/**
 * Document Interface
 *
 * A Document is an object which has content with a measuable size (in bytes)
 * and a MIME type.
 * @package File
 */

interface Document {
	// content
	public function getContent();
	public function getContentType();
}

?>
