<?php

//------------------------------------------------------------------------------
// VirtualFile
//------------------------------------------------------------------------------

abstract class VirtualFile implements WriteableFile {
	// file properties
	protected $path = '';
	protected $permissions = 0644;	
	protected $parent = null;
	protected $mtime = 0;
	protected $atime = 0;

	function __construct($path = null) {
		// set the path
		$this->path = $path;
	}
	
	// path

	public function getPath() {
		return $this->path;
	}
	
	public function getFilename() {
		return basename($this->path);
	}
	
	// permissions

	public function getPermissions() {
		return $this->permissions;
	}

	public function setPermissions($permissions) {
		return $this->permissions = (int) $permissions;
	}
	
	// parent

	public function getParent() {
		return $this->parent;
	}

	// modification time
	
	public function getModificationTime() {
		return $this->mtime;
	}

	public function setModificationTime($time) {
		return $this->mtime = $time;
	}
	
	// access time
	
	public function getAccessTime() {
		return $this->atime;
	}
	
	public function setAccessTime($time) {
		return $this->atime = $time;
	}
}

?>