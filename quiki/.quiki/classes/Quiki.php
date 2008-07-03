<?php

class Quiki
{
	//----------------------------------------------------------------------
	// users
	//----------------------------------------------------------------------
	
	static public function getAuthenticatedUser($request) {
		// return the current logged-in user
		return $request->verifyAuthentication(array('Quiki', 'authenticateUser'));
	}
	
	static public function authenticateUser($type, $args) {
		try {
			// check authentication
			if ($type == 'Basic') {
				// basic authentication
				$user = new User($args['username']);
				if ($user && $user->password == md5($args['password']))
					return $user;
			}
		} catch (Exception $e) {}
		
		// unknown or failed authentication
		return false;
	}
	
	//----------------------------------------------------------------------
	// database
	//----------------------------------------------------------------------

//[TODO] load from INI file
	const DB_DSN = 'mysql:host=%s;dbname=%s';
	const DB_HOST = 'localhost';
	const DB_NAME = 'quiki';
	const DB_USERNAME = 'root';
	const DB_PASSWORD = '';	

	static public function getDBConnection()
	{
		// return a new database connection
		$dsn = sprintf(Quiki::DB_DSN, Quiki::DB_HOST, Quiki::DB_NAME);
		return new PDO($dsn, Quiki::DB_USERNAME, Quiki::DB_PASSWORD);
	}
}

?>
