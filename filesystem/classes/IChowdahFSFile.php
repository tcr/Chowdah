<?php

//==============================================================================
// chowdah filesystem classes
//==============================================================================

interface IChowdahFSFile {
	// get the file's chowdah index entry
	public function getIndexEntry($create = false);
}

?>