<?php

// initialize Chowdah
require 'chowdah/Chowdah.php';
	
// import classes
import('classes');
import('resources');
import('textile');

// get installation or root resource
$resource = (is_file('quiki.ini') ? new RootResource() : new InstallationResource());

// load chowdah
Chowdah::init();
Chowdah::handleCurrentRequest($resource)->send();
	
?>