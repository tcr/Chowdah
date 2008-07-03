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

	static public function getDBConnection()
	{
		// load configuration file
		$config = new INIFile('quiki.ini');
	
		// return a new database connection
		return new PDO(sprintf($config->getValue('db.dsn'), $config->getValue('db.host'),
		    $config->getValue('db.name')), $config->getValue('db.user'), $config->getValue('db.password'));
	}
}

?>
