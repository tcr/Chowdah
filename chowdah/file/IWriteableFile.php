<?php

/**
 * Writeable File Interface
 * @package File
 */

interface IWriteableFile extends IFile {
	// properties
	public function setPermissions($permissions);
	public function setModificationTime($time);
	public function setAccessTime($time);	
}

?>