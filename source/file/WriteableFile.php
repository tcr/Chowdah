<?php

/**
 * Writeable File Interface
 * @package File
 */

interface WriteableFile extends File {
	// properties
	public function setPermissions($permissions);
	public function setModificationTime($time);
	public function setAccessTime($time);	
}

?>