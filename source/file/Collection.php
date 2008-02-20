<?php

/**
 * Collection Interface
 *
 * A Collection is an object which has children, which can be retrieved via the
 * <kbd>getChild</kbd> method.
 * @package File
 */

interface Collection {
	/**
	 * Get a child object.
	 *
	 * Gets a child of this Collection with the speicifed filename.
	 * @param string The file name of the child to be returned.
	 */
	public function getChild($filename);
	
	// getChildren flags
#	public function getChildren($flags = null);
	const ONLY_DOCUMENTS = 1;
	const ONLY_COLLECTIONS = 2;
}

?>