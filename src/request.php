<?php

//##############################################################################
// Chowdah | REST Framework
//##############################################################################
// written by Tim Ryan (tim-ryan.com)
// we are not hackers!
//##############################################################################

$importFolders = array();

function import_autoload($class) {
	global $importFolders;
	foreach ($importFolders as $folder) {
		if (is_file($folder . '/' . $class . '.php'))
			include_once $folder . '/' . $class . '.php';
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
import('chowdah/file');
import('chowdah/http');

// load Chowdah
Chowdah::init();
Chowdah::loadApplication(getcwd());

// call handler
Handler::call(HTTPRequest::getCurrent());

?>