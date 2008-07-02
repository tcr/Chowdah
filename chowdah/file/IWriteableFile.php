<?php

/**
 * Writeable File Interface
 * @package chowdah.file
 */

interface IWriteableFile extends IFile {
	// properties
	public function setPermissions($permissions);
	public function setModificationTime($time);
	public function setAccessTime($time);	
}

?>