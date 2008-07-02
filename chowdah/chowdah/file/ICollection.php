<?php

/**
 * Collection Interface
 *
 * A Collection is an object which has children, which can be retrieved via the
 * <kbd>getChild</kbd> method.
 * @package chowdah.file
 */

interface ICollection {
	/**
	 * Get a child object.
	 *
	 * Gets a child of this Collection with the speicifed filename.
	 * @param string The file name of the child to be returned.
	 */
	public function getChild($filename);
}

?>
