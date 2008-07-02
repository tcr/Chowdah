<?php

#[TODO] extend from independent FSDocumentResource class?
#[TODO] creating resource from DOCUMENT_ROOT could throw exception, so...

// load Chowdah class
require 'chowdah/Chowdah.php';
	
// import classes
import('.');
import('classes');
import('resources');

// get the document root
$root = new ChowdahFSCollection($_SERVER['DOCUMENT_ROOT']);

// load Chowdah
Chowdah::init();
Chowdah::handleCurrentRequest(new ChowdahFSCollectionResource($root))->send();
	
?>
