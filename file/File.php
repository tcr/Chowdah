<?php

//##############################################################################
// File Classes for PHP5 (file.php)
// allows manipulation of files and the filesystem
//##############################################################################
// written by Tim Ryan (tim-ryan.com)
// released under the MIT/X License
// we are not hackers!
//##############################################################################

#[TODO] ->clone($source) function
#[TODO] better utilize SPL functions

//------------------------------------------------------------------------------
// File interface
//------------------------------------------------------------------------------

interface File {
	// file relations
	public function getParent();

	// path
	public function getPath();
	public function getFilename();
	
	// properties
	public function getPermissions();
	public function getModificationTime();
	public function getAccessTime();
}

?>