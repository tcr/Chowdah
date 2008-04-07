<?php

/**
 * File Interface
 * @package File
 */
 
#[TODO] ->clone($source) function
#[TODO] better utilize SPL functions

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