<?php

//------------------------------------------------------------------------------
// Chowdah Index File
//------------------------------------------------------------------------------

#[TODO] advanced checking for invalid keys

class ChowdahIndexFile {
	// properties
	protected $node;
	protected $filename = '';

	function __construct(DOMElement $node) {
		// save DOM node
		$this->node = $node;
		// save the filename
		$xpath = new DOMXPath($this->node->ownerDocument);
		$this->filename = $xpath->evaluate('string(.)', $node);
	}
	
	// filename
	
	public function getFilename() {
		return $this->filename;
	}

	// metadata
	
	public function getMetadata($key) {
		// get the metadata value
		return $this->node->hasAttribute($key) ? $this->node->getAttribute($key) : false;
	}
	
	public function setMetadata($key, $value) {
		// validate the key
		if (!strlen($key))
			return false;
		// set a new metadata value
		return $this->node->setAttribute($key, $value);
	}
	
	public function deleteMetadata($key) {
		// delete the metadata value
		return $this->node->removeAttribute($key);
	}
	
	// magic methods
	
	function __get($key) {
		switch ($key) {
		    case 'metadata':
			// create an array of metadata
			$metadata = array();
			foreach ($this->node->attributes as $attr)
				$metadata[$attr->name] = $attr->value;
			return $metadata;
		}
	}
}

?>