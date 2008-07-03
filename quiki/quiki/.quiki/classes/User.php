<?php

class User {
	protected $name;
	protected $password;
	protected $email;
	protected $registered;

	function __construct($name) {
		// get data for this object
		User::$readQuery->execute(array($name));
		if (!($data = User::$readQuery->fetch()))
			throw new Exception('No user with the username "' . $name . '" was found.');
		User::$readQuery->closeCursor();
		
		// save the properties
		foreach ($data as $key => $value)
			$this->$key = $value;
	}
	
	public function update($name = null, $password = null, $email = null) {
		// get database name
		$dbName = $this->name;
		
		// get parameters
		if ($name !== null)
			$this->name = $name;
		if ($password !== null)
			$this->password = $password;
		if ($email !== null)
			$this->email = $email;
		
		// update database
		User::$updateQuery->execute(array($this->name, $this->password, $this->email, $dbName));
	}
	
	public function delete() {
		// delete this object
		return (bool) User::$deleteQuery->execute(array($this->name));
	}
	
	function __get($key) {
		// return protected values
		if (isset($this->$key))
			return $this->$key;
		return false;
	}
	
	//----------------------------------------------------------------------
	// static functions
	//----------------------------------------------------------------------
	
	static public function create($name, $password, $email) {
		// validate data
		if (!preg_match('/^[A-Za-z_][A-Za-z_0-9\-]{0,254}$/', $name))
			throw new Exception('Please enter a valid username. A username must start with a letter, and can only contain alphanumeric characters, except for `_` and `-`.');
		if (!$password)
			throw new Exception('Please enter a valid password.');
		if (!$email)
			throw new Exception('Please enter a valid e-mail address.');
	
		// create new user
		User::$createQuery->execute(array($name, $password, $email));
		return new User($name);
	}
	
	static public function register($name, $email) {
		// check if a user with this username exists
		if (count(User::getList(array('name' => $name))))
			throw new Exception('A user with the username "' . $name . '" already exists.');
		// check if a user with this username exists
		if (count(User::getList(array('email' => $email))))
			throw new Exception('A user has already been registered with the e-mail address "' . $email . '."');
	
		// create a temporary password
		for ($i = 0, $passchars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; $i < 16; $i++)
		    $password[] = $passchars[rand(0, strlen($passchars) - 1)];
		$password = implode('', $password);
		
		// create the new account
		User::create($name, $password, $email);

		// send validation e-mail
		if (!@mail($email,
			'Quiki account registration',
			"You've registered an account with Quiki. Your login credentials are as follows:\n\n" .
			    "\tUsername: " . $name . "\n" .
			    "\tPassword: " . $password . "\n\n" .
			    "You may now log in and edit your account or change your password.",
			"From: Quiki Admin <quiki@chowdah.googlecode.com>\r\n"
		    ))
			throw new Exception('The account confirmation could not be sent. Please enter a valid e-mail address.');
	
		// return the temporary password
		return $password;
	}
	
	static public function getList($search = array(), $start = 0, $limit = 999999) {
		// create where clause
		$where = '';
		foreach (array_keys($search) as $key)
			$where .= (!strlen($where) ? ' WHERE ' : ' AND ') . $key . ' = ?';
		// generate unique query
		$query = User::$dbh->prepare('SELECT `name` FROM `users` ' .
		    $where . 'ORDER BY `name` ASC LIMIT ?, ?');
		// bind parameters
		for ($i = 0, $values = array_values($search); $i < count($values); $i++)
			$query->bindValue($i + 1, $values[$i]);
		$query->bindValue($i++ + 1, $start, PDO::PARAM_INT);
		$query->bindValue($i++ + 1, $limit, PDO::PARAM_INT);
		
		// create objects
		$users = array();
		$query->execute();
		foreach ($query->fetchAll() as $user)
			$users[] = new User($user['name']);
		return $users;
	}
	
	protected static $dbh;
	protected static $createQuery;
	protected static $readQuery;
	protected static $updateQuery;
	protected static $deleteQuery;
	
	static public function init() {
		// initialize database handlers
		User::$dbh = Quiki::getDBConnection();
		User::$createQuery = User::$dbh->prepare('INSERT INTO users (name, password, email, registered) VALUES (?, MD5(?), ?, NOW())');
		User::$readQuery = User::$dbh->prepare('SELECT *, UNIX_TIMESTAMP(registered) FROM `users` WHERE `name` = ?');
		User::$updateQuery = User::$dbh->prepare('UPDATE `users` SET `name` = ?, `password` = ?, `email` = ? WHERE `name` = ?');
		User::$deleteQuery = User::$dbh->prepare('DELETE FROM `users` WHERE `name` = ? LIMIT 1');
	}
}

// initialize database handlers
User::init();

?>