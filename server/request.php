<?php

// initialize Chowdah
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