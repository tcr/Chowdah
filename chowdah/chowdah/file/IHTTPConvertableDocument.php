<?php

/**
 * HTTP-Convertable Document Interface
 * @package chowdah.file
 */

interface IHTTPConvertableDocument extends IDocument {
	public function getHTTPMessageContent(HTTPMessage $message);
	public function setHTTPMessageContent(HTTPMessage $message);
}

?>