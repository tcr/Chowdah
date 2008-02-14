<?php

//------------------------------------------------------------------------------
// WriteableDocument interface
//------------------------------------------------------------------------------

interface WriteableDocument extends Document {
	public function setContent($data);
	public function setContentType(MIMEType $mimetype);
}

?>