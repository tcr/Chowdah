<?php

/**
 * Document Interface
 *
 * A Document is an object which has content with a measuable size (in bytes)
 * and a MIME type.
 * @package chowdah.file
 */

interface IDocument {
	// content
	public function getContent();
	public function getContentType();
}

?>
