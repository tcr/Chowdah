<?php

/**
 * Filesystem Collection Class
 * @package chowdah.file
 */

class FSCollection extends FSFile implements IWriteableCollection, IFiniteCollection, ArrayAccess, IteratorAggregate, Countable {
	function __construct($path, $context = null) {
		// call parent constructor
		parent::__construct($path, $context);
		// check that the target is a collection
		if (!is_dir($this->path))
			throw new Exception('The file located at "' . $path . '" is not a directory.');
	}
	
	//------------------------------------------------------------------
	// collection children
	//------------------------------------------------------------------
	
	public function getChild($filename) {
		// return false on '.' or '..'
		if (in_array($filename, array('.', '..')))
			return false;
		// return the child file
		return $this->getFileFromPath($this->path . '/' . basename($filename));
	}
	
	public function getChildren($flag = null) {
		// apply class checking
		$class = $flag == IFiniteCollection::CHILD_DOCUMENTS ? 'Document' :
		    ($flag == IFiniteCollection::CHILD_COLLECTIONS ? 'Collection' : 'File');
		// create an array of children
		$children = array();
		foreach (new DirectoryIterator($this->getPath()) as $file)
			if (($child = $this->getChild($file->getFilename())) && ($child instanceof $class))
				$children[$file->getFilename()] = $child;
		ksort($children);
		return $children;
	}

	//------------------------------------------------------------------
	// collection modification
	//------------------------------------------------------------------
	
	public function createChildDocument($filename, $overwrite = false, $permissions = 0644) {
		// normalize the paths
		if (!strlen(basename($filename)) || in_array($filename, array('.', '..')))
			throw new Exception('Invalid filename "' . $filename . '" supplied.');
		$filename = basename($filename);
		$path = $this->getPath() . '/' . $filename;
		
		// prevent from overwriting any existing file
		if (!$overwrite && $this->getChild($filename))
			return false;
		else if ($overwrite)
			$this->deleteChild($filename);
			
		// try to create the file
		if (file_put_contents($path, '') === false)
			throw new Exception('The document at "' . $path . '" could not be created.');
		// return the new file object
		$file = $this->getFileFromPath($path);
		$file->setPermissions($permissions);
		return $file;
	}


	public function createChildCollection($filename, $overwrite = false, $permissions = 0755) {
		// normalize the paths
		if (!strlen(basename($filename)) || in_array($filename, array('.', '..')))
			throw new Exception('Invalid filename "' . $filename . '" supplied.');
		$filename = basename($filename);
		$path = $this->getPath() . '/' . $filename;
		
		// prevent from overwriting any existing file
		if (!$overwrite && $this->getChild($filename))
			return false;
		else if ($overwrite)
			$this->deleteChild($filename);
			
		// try to create the file
		if (!(mkdir($path, $permissions)))
			throw new Exception('The document at "' . $path . '" could not be created.');
		// return the new file object
		$file = $this->getFileFromPath($path);
		return $file;
	}

	public function deleteChild($filename) {
		// normalize the paths
		if (!strlen(basename($filename)) || in_array($filename, array('.', '..')))
			throw new Exception('Invalid filename "' . $filename . '" supplied.');
		$filename = basename($filename);
		$path = $this->getPath() . '/' . $filename;
		
		// try to delete as a file
		if (is_file($path))
			return unlink($path);
		// try to delete as a directory
		if (is_dir($path)) {
			$iterator = new RecursiveDirectoryIterator($path);
			foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file) {
				if (!$file->isDir())
					unlink($file->getPathname());
				else
					rmdir($file->getPathname());
			}
			return rmdir($path);
		}
		// could not delete file
		return false;
	}

	public function move(File $file, $overwrite = false, $filename = null) {
		// normalize the paths
		if ($filename === null)
			$filename = $file->getFilename();
		else if (!strlen($filename = basename($filename)) || in_array($filename, array('.', '..')))
			throw new Exception('Invalid filename "' . $filename . '" supplied.');
		$target = $this->path . '/' . $filename;

		// avoid moving to original location
		if ($target == $file->getPath())
			return true;

		// check if file can be overwritten
		if ($this->getChild($filename)) {
			if (!$overwrite)
				return false;
			else
				$this->deleteChild($filename);
		}

		// move the file to this collection
		if (!@rename($file->getPath(), $target))
			throw new Exception('The file "' . $file->getPath() . '" could not be moved.');
		// reconstruct the file object
		$file->__construct($target, $file->getContext());
		return true;
	}

	public function copy(File $file, $overwrite = false, $filename = null) {
		// normalize the paths
		if ($filename === null)
			$filename = $file->getFilename();
		else if (!strlen($filename = basename($filename)) || in_array($filename, array('.', '..')))
			throw new Exception('Invalid filename "' . $filename . '" supplied.');
		$target = $this->path . '/' . $filename;

		// avoid moving to original location
		if ($target == $file->getPath())
			return true;

		// check if file can be overwritten
		if ($this->getChild($filename)) {
			if (!$overwrite)
				return false;
			else
				$this->deleteChild($filename);
		}

		// copy the file
		if ($file instanceof FSDocument) {
			// copy the file object to this folder
			if (!copy($file->getPath(), $target))
				throw new Exception('The file "' . $file->getPath() . '" could not be copied.');
		} else if ($file instanceof FSCollection) {
			// create a new child directory
			$dir = $this->createChildCollection($filename);
			// copy children
			foreach ($file->getChildren() as $child)
				if ($child->getPath() != $dir->getPath())
					$dir->copy($child);
		}

		// reconstruct the file object
		$file->__construct($target, $file->getContext());
		return true;
	}

	//------------------------------------------------------------------
	// array access functions
	//------------------------------------------------------------------

	function offsetExists($offset) {
		// return true if the file exists
		return (bool) $this->offsetGet($offset);
	}   
  
	function offsetGet($offset) {
		// return a child of the name $offset
		return $this->getChild($offset);
	}

	function offsetSet($offset, $value) {
		// cannot set child of a collection
		throw new Exception('Cannot set the child of a collection. (Use FSCollection::move or FSCollection::copy)');
	}

	function offsetUnset($offset) {
		// cannot unset child of a collection
		throw new Exception('Cannot unset the child of a collection. (Use FSCollection::deleteChild)');
	}

	function getIterator() {
		// return an iterator over the children array
		return new ArrayIterator($this->getChildren());
	}
	
	function count() {
		// return the count of all children in this directory
		return count($this->getChildren());
	}
}

?>