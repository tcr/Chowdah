<?php

/**
 * Writeable Collection Interface
 * @package File
 */

interface WriteableCollection extends Collection {	
	// file modification	
	public function createChildDocument($filename, $overwrite = false, $permissions = 0644);
	public function createChildCollection($filename, $overwrite = false, $permissions = 0755);
	public function deleteChild($filename);
	public function move(File $file, $replace = false, $filename = null);
	public function copy(File $file, $replace = false, $filename = null);
}

?>