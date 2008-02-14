<?php

//------------------------------------------------------------------------------
// VirtualCollection
//------------------------------------------------------------------------------

class VirtualCollection extends VirtualFile implements WriteableCollection, ArrayAccess, IteratorAggregate, Countable {
	// file properties
	protected $children = array();
	
	//------------------------------------------------------------------
	// collection children
	//------------------------------------------------------------------
	
	public function getChild($filename) {
		return isset($this->children[$filename]) ? $this->children[$filename] : false;
	}
	
	public function getChildren($flags = null) {
		// apply class checking
		$class = $flag == Collection::ONLY_DOCUMENTS ? 'Document' :
		    ($flag == Collection::ONLY_COLLECTIONS ? 'Collection' : 'File');
		// create an array of children
		$children = array();
		foreach ($this->children as $file => $child)
			if ($child instanceof $class)
				$children[$file] = $child;
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
		
		// create the file object
		$file = new VirtualDocument($path);
		$file->setPermissions($permissions);
		return $this->children[$filename] = $file;
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
			
		// create the file object
		$file = new VirtualCollection($path);
		$file->setPermissions($permissions);
		return $this->children[$filename] = $file;
	}

	public function deleteChild($filename) {
		// normalize the paths
		if (!strlen(basename($filename)) || in_array($filename, array('.', '..')))
			throw new Exception('Invalid filename "' . $filename . '" supplied.');
		
		// delete the file
		if (!$this->getChild($filename))
			return false;
		unset($this->children[$filename]);
		return true;
	}

	public function move(File $file, $overwrite = false, $filename = null) {
		// normalize the paths
		if ($filename === null)
			$filename = $file->getFilename();
		if (!strlen($filename = basename($filename)) || in_array($filename, array('.', '..')))
			throw new Exception('Invalid filename "' . $filename . '" supplied.');
		$target = $this->path . '/' . $filename;

		// check if file can be overwritten
		if ($this->getChild($filename)) {
			if (!$overwrite)
				return false;
			else
				$this->deleteChild($filename);
		}
		
		// delete the old child
		if ($file->getParent())
			$file->getParent()->deleteChild($file->getFilename());
		// move the file to this collection
		$this->children[$filename] = $file;
		$file->setParent($this);
		$file->setPath($target);
		return true;
	}

	public function copy(File $file, $overwrite = false, $filename = null) {
		// normalize the paths
		if ($filename === null)
			$filename = $file->getFilename();
		if (!strlen($filename = basename($filename)) || in_array($filename, array('.', '..')))
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

		// copy the file to this collection
		$class = get_class($file);
		$newFile = new $class($target);
		$this->children[$filename] = $newFile;
		$newFile->setParent($this);
		$newFile->setPath($target);
		// clone file properties
		$newFile->clone($file);
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