<?php

/**
 * File Interface
 * @package chowdah.file
 */
 
#[TODO] ->clone($source) function
#[TODO] better utilize SPL functions

interface IFile {
	// file relations
	public function getParent();

	// path
	public function getPath();
	public function getFilename();
	
	// properties
	public function getPermissions();
	public function getModificationTime();
	public function getAccessTime();
	public function getSize();
}

?>
