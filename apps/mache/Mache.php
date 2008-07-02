<?php

class Mache {	
	//----------------------------------------------------------------------
	// database
	//----------------------------------------------------------------------
	
	const DB_NAME = 'mache';
	const DB_USERNAME = 'root';
	const DB_PASSWORD = '';

	static public function getDBConnection() {
		return new PDO('mysql:host=localhost;dbname=' . Mache::DB_NAME,
		    Mache::DB_USERNAME, Mache::DB_PASSWORD);
	}
}

?>
