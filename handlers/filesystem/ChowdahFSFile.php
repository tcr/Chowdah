<?php

//==============================================================================
// chowdah filesystem classes
//==============================================================================

interface ChowdahFSFile {
	// get the file's chowdah index entry
	public function getIndexEntry($create = false);
}

?>