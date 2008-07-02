<?php

//------------------------------------------------------------------------------
// import files
//------------------------------------------------------------------------------

#[TODO] maybe more package-like emulation

$importFolders = array();

function import_autoload($class) {
	global $importFolders;
	foreach ($importFolders as $folder) {
		if (is_file($folder . '/' . $class . '.php'))
			include_once $folder . '/' . $class . '.php';
	}
}

// setup import loader
spl_autoload_register('import_autoload');

function import($folder) {
	// add folder to __autoload list
	global $importFolders;
	$importFolders[] = @realpath($folder);
}

?>