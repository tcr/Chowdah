<?php

class InstallationResource extends QuikiResourceBase
{
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	protected $methods = array('GET', 'OPTIONS', 'POST');

	public function GET(HTTPRequest $request)
	{
		// create an XML respresentation of this object
		$doc = new SimpleXMLElement('<installation />');
		$doc->{'db-host'} = 'localhost';
		$doc->{'db-name'} = 'quiki';
		// create and send the response
		return $this->formatResponse($request, new HTTPResponse(), $doc);
	}

	public function POST(HTTPRequest $request)
	{
		// create the INI file
		$file = new INIFile();
		$file->setValue('db.dsn', 'mysql:host=%s;dbname=%s');
#[TODO] not... hardcode that
		$file->setValue('db.host', $request->parsedContent['db-host']);
		$file->setValue('db.user', $request->parsedContent['db-user']);
		$file->setValue('db.password', $request->parsedContent['db-password']);
		$file->setValue('db.name', $request->parsedContent['db-name']);
		$file->save('quiki.ini');

		try
		{
			// validate data
			if (!$file->getValue('db.name'))
				throw new Exception('Please enter in a valid database name.');
			// attempt to connect to the database
			$dbh = new PDO(sprintf($file->getValue('db.dsn'), $file->getValue('db.host'),
			    $file->getValue('db.name')), $file->getValue('db.user'), $file->getValue('db.password'));

			// run the install script
			$dbh->exec(file_get_contents('data/install.sql'));
			if ($dbh->errorCode() != '00000') {
				$error = $dbh->errorInfo();
				throw new Exception($error[2]);
			}

			// create the new user
			User::create($request->parsedContent['account-username'],
			    $request->parsedContent['account-password'],
			    $request->parsedContent['account-email']);

			// display the main page
			$root = new RootResource();
			return $root->GET($request);
		}
		catch (Exception $e)
		{
			// delete ini file
			unlink('quiki.ini');
			
			// show there was an error in the installation
			$doc = new SimpleXMLElement('<installation />');
			$doc['error'] = $e->getMessage();
			foreach ($request->parsedContent as $key => $value)
				$doc->{$key} = $value;
			// create and send the response
			return $this->formatResponse($request, new HTTPResponse(), $doc);
		}
	}
}

?>
