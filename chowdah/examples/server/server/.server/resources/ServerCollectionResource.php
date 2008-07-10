<?php

//------------------------------------------------------------------------------
// server collection resource
//------------------------------------------------------------------------------

class ServerCollectionResource extends CollectionResource
{
	protected $index = null;

	function __construct(ServerCollection $file)
	{
		// save the internal object
		parent::__construct($file, $file->getMetadata('allow_directory_list'));
		
		// get directory index
		if ($this->file->getMetadata('index'))
			$this->index = $this->getChild($this->file->getMetadata('index'));
	}
	
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	public function getAllowedMethods()
	{
		// return the methods of the index, or this collection
		return ($this->index ? $this->index->getAllowedMethods() : array('GET'));
	}
	
	public function handle(HTTPRequest $request)
	{
		// return the response of the index, or this collection
		return ($this->index ? $this->index->handle($request) : parent::handle($request));
	}

	//----------------------------------------------------------------------
	// collection
	//----------------------------------------------------------------------

	public function getChild($filename)
	{
		// get the child file (while not displaying hidden files)
		if ($filename[0] == '.' || !($child = $this->file->getChild($filename)))
			return false;
			
		// return the child object
		return ($child instanceof ICollection ?
		        new ServerCollectionResource($child) :
		        new ServerDocumentResource($child));
	}
}

?>
