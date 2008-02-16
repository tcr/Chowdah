<?php

//------------------------------------------------------------------------------
// Document interface
//------------------------------------------------------------------------------

interface Document {
	// content
	public function getContent();
	public function getContentType();
	
	// properties
	public function getSize();
}

?>