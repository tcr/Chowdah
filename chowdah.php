<?php

// i guess
$GLOBALS['importFolders'] = array();

function import_autoload($class) {
	foreach ($GLOBALS['importFolders'] as $folder)
		@include $folder . '/' . $class . '.php';
}

spl_autoload_register('import_autoload');

function import($folder) {
	// add folder to __autoload list
	$GLOBALS['importFolders'][] = @realpath($folder);
}

//==============================================================================
// Chowdah entry
//==============================================================================

import('chowdah');
import('file');
import('http');

// initialize Chowdah
Chowdah::init();

// find the specified handler
parse_str($_SERVER['argv'][0], $args);
if (!strlen($args['handler']))
	throw new HTTPStatusException(500, null, 'No handler could be found for the Chowdah application.');
// import handler
import ('handlers/' . $args['handler']);

// call handler
Handler::call(HTTPRequest::getCurrent());

?>