<?php

//------------------------------------------------------------------------------
// Collection interface
//------------------------------------------------------------------------------

interface Collection {
	// children
#	public function getChildren($flags = null);
	public function getChild($filename);
	
	// getChildren flags
	const ONLY_DOCUMENTS = 1;
	const ONLY_COLLECTIONS = 2;
}

?>