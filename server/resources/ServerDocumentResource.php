<?php

//------------------------------------------------------------------------------
// server document resource
//------------------------------------------------------------------------------

class ServerDocumentResource extends DocumentResource {
	function __construct(ServerDocument $file) {
		// save the internal object
		parent::__construct($file);
	}
}

?>
