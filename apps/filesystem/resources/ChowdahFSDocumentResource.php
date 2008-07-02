<?php

//------------------------------------------------------------------------------
// chowdah filesystem document resource
//------------------------------------------------------------------------------

class ChowdahFSDocumentResource extends FSDocumentResource {
	function __construct(ChowdahFSDocument $file) {
		// save the internal object
		parent::__construct($file);
	}
}

?>
