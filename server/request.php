<?php

#[TODO] creating resource from DOCUMENT_ROOT could throw exception, so...

// load Chowdah class
require 'chowdah/Chowdah.php';
	
// import classes
import('classes');
import('resources');

// get the document root
$root = new ServerCollection($_SERVER['DOCUMENT_ROOT']);

// load Chowdah
Chowdah::init();
Chowdah::handleCurrentRequest(new ServerCollectionResource($root))->send();
	
?>
