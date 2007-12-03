<?php

//##############################################################################
// File Classes for PHP5 (file.php)
// allows manipulation of files and the filesystem
//##############################################################################
// written by Tim Ryan (tim-ryan.com)
// released under the MIT/X License
// we are not hackers!
//##############################################################################

#[TODO] ->clone($source) function
#[TODO] better utilize SPL functions

//==============================================================================
// file interfaces
//==============================================================================

//------------------------------------------------------------------------------
// Document interface
//------------------------------------------------------------------------------

interface Document {
	// content
	public function getContent();
	public function getContentType();
	
	// properties
	public function getSize();
}

interface WriteableDocument extends Document {
	public function setContent($data);
	public function setContentType(MIMEType $mimetype);
}

//------------------------------------------------------------------------------
// Collection interface
//------------------------------------------------------------------------------

interface Collection {
	// children
#	public function getChildren($flags = null);
	public function getChild($filename);
	
	// getChildren flags
	const ONLY_DOCUMENTS = 1;
	const ONLY_COLLECTIONS = 2;
}

interface WriteableCollection extends Collection {	
	// file modification	
	public function createChildDocument($filename, $overwrite = false, $permissions = 0644);
	public function createChildCollection($filename, $overwrite = false, $permissions = 0755);
	public function deleteChild($filename);
	public function move(File $file, $replace = false, $filename = null);
	public function copy(File $file, $replace = false, $filename = null);
}

//------------------------------------------------------------------------------
// File interface
//------------------------------------------------------------------------------

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

interface WriteableFile extends File {
	// properties
	public function setPermissions($permissions);
	public function setModificationTime($time);
	public function setAccessTime($time);	
}

//==============================================================================
// virtual file classes
//==============================================================================

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

//------------------------------------------------------------------------------
// VirtualDocument
//------------------------------------------------------------------------------

class VirtualDocument extends VirtualFile implements WriteableDocument {
	// file properties
	protected $content = '';
	protected $contentType = null;

	// content

	public function getContent() {
		return $this->content;
	}

	public function setContent($data) {
		return $this->content = (string) $data;
	}
	
	// content type
	
	public function getContentType() {
		// attempt to read the content type
		if ($this->contentType)
			return $this->contentType;
		else
			return new MIMEType('application', 'octet-stream');
	}
	
	public function setContentType(MIMEType $type) {
		$this->contentType = $type;
	}

	// file size

	public function getSize() {
		return strlen($this->content);
	}
}

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

//==========================================================================
// FSFile class
//==========================================================================

abstract class FSFile implements WriteableFile {
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

//==========================================================================
// FSDocument class
//==========================================================================

class FSDocument extends FSFile implements WriteableDocument {
	function __construct($path, $context = null) {
		// call parent constructor
		parent::__construct($path, $context);
		// check that the target is a document
		if (!is_file($this->path))
			throw new Exception('The file located at "' . $path . '" is not a document.');
	}
	
	// content

	public function getContent() {
		return file_get_contents($this->getPath(), false, $this->context);
	}

	public function setContent($data) {
		return file_put_contents($this->getPath(), $data, null, $this->context);
	}
	
	// content type
	
	public function getContentType() {
		// attempt to read the content type
		if (function_exists('mime_content_type'))
			return mime_content_type($this->getPath());
		else if (function_exists('finfo_open'))
			return MIMEType::parse(finfo::file($this->getPath(),
			    FILEINFO_MIME, $this->getContext()));
		else if ($mimetype = MIMEType::parse(@exec('file -bi ' . escapeshellarg($this->getPath()))))
			return $mimetype;
		else
			return new MIMEType('application', 'octet-stream');
	}
	
	public function setContentType(MIMEType $mimetype) {
		// can't set the content type
		return false;
	}

	// file size

	public function getSize() {
		return filesize($this->path);
	}
}

//==========================================================================
// FSCollection class
//==========================================================================

class FSCollection extends FSFile implements WriteableCollection, ArrayAccess, IteratorAggregate, Countable {
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
		$class = $flag == Collection::ONLY_DOCUMENTS ? 'Document' :
		    ($flag == Collection::ONLY_COLLECTIONS ? 'Collection' : 'File');
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