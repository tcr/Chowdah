<?php

/**
 * Finite Collection Interface
 *
 * A Collection is an object which has a finite number children, which can be
 * retrieved individually via the <kbd>getChild</kbd> method or in an array via
 * the <kbd>getChildren</kbd> method and its flags.
 * @package chowdah.file
 */

interface IFiniteCollection extends ICollection {
	/**
	 * Get an array of children.
	 *
	 * Returns an array of the children of this Collection matching the
	 * specified flags.
	 * @param integer The type of children to return. This can be either
	 * FiniteCollection::CHILD_DOCUMENTS or
	 * FiniteCollection::CHILD_COLLECTIONS.
	 */
	public function getChildren($flags = null);	

	// getChildren flags
	const CHILD_DOCUMENTS = 1;
	const CHILD_COLLECTIONS = 2;
}

?>