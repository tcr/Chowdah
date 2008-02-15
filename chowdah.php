<?php

//##############################################################################
// Chowdah | REST Framework
//##############################################################################
// written by Tim Ryan (tim-ryan.com)
// released under the MIT/X License
// we are not hackers!
//##############################################################################

$importFolders = array();

function import_autoload($class) {
	global $importFolders;
	foreach ($importFolders as $folder) {
		if (is_file($folder . '/' . $class . '.php')) {
			include_once $folder . '/' . $class . '.php';
			return;
		}
	}
}

spl_autoload_register('import_autoload');

function import($folder) {
	// add folder to __autoload list
	global $importFolders;
	$importFolders[] = @realpath($folder);
}

//==============================================================================
// Chowdah entry
//==============================================================================

import('chowdah');
import('file');
import('http');

// initialize Chowdah
Chowdah::init();

// get specified handler
if (!($handler = Chowdah::getArgument('handler')))
	$handler = 'default';
// import handler files
import('handlers/' . $handler);

// call handler
Handler::call(HTTPRequest::getCurrent());

?>