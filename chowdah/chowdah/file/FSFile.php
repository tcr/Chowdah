<?php

/**
 * Filesystem File Abstract Class
 * @package chowdah.file
 */

abstract class FSFile implements IWriteableFile {
	// file path
	protected $path = '';
	// stream context
	protected $context = null;

	function __construct($path, $context = null) {
		// check that the path exists
		if (($this->path = realpath($path)) === false)
			throw new Exception('The file located at "' . $path . '" does not exist.');
		// set the context
		$this->context = $context;
	}
	
	// path

	public function getPath() {
		return $this->path;
	}
	
	public function getFilename() {
		return basename($this->path);
	}
	
	// context

	public function getContext() {
		return $this->context;
	}
	
	// permissions

	public function getPermissions() {
		return fileperms($this->path);
	}

	public function setPermissions($permissions) {
		return chmod($this->path, $permissions);
	}
	
	// parent

	public function getParent() {
		return $this->getFileFromPath(dirname($this->path));
	}

	// modification time
	
	public function getModificationTime() {
		return filemtime($this->path);
	}

	public function setModificationTime($time) {
		return touch($this->path, $time);
	}
	
	// access time
	
	public function getAccessTime() {
		return filemtime($this->path);
	}
	
	public function setAccessTime($time) {
		return touch($this->path, $this->getModificationTime(), $time);
	}
	
	// size
	
	public function getSize() {
		return filesize($this->path);
	}
	
	// file paths
	
	public function getRelativePath(File $target) {
		// get the relative path between this file and the target
		$source = explode('/', $this instanceof FileCollection ? $this->getPath() : dirname($this->getPath()));
		$target = explode('/', $target->getPath());
		
		// find beginning offset of difference
		for ($i = 0; $i < count($source) && $source[$i] == $target[$i]; $i++);
		// ascend tree
		$path = str_repeat('../', count($source) - $i);
		// descend tree
		$path .= implode('/', array_slice($target, $i));
		// return the path
		return $path;
	}

	protected function getFileFromPath($path) {
		// try and return an FSFile object of the specified path
		try {
			if (is_file($path))
				return new FSDocument($path);
			if (is_dir($path))
				return new FSCollection($path);
		} catch (Exception $e) { }
		
		// no match found
		return false;
	}
}

?>