<?php

class LoginResource extends QuikiResourceBase
{
	//----------------------------------------------------------------------
	// HTTP methods
	//----------------------------------------------------------------------
	
	protected $methods = array('GET', 'OPTIONS', 'POST');

	public function GET(HTTPRequest $request)
	{
		// create an XML respresentation of this object
		$doc = new SimpleXMLElement('<installation />');
		// create and send the response
		return $this->formatResponse($request, new HTTPResponse(), $doc);
	}

	public function POST(HTTPRequest $request)
	{
		// get submitted data
		$dbDSN = 'mysql:host=%s;dbname=%s';
#[TODO] not... hardcode that
		$dbHost = $request->parsedContent['host'];
		$dbUser = $request->parsedContent['user'];
		$dbPassword = $request->parsedContent['password'];
		$dbName = $request->parsedContent['name'];

		try
		{
			// attempt to connect to the database
			$dbh = PDO(sprintf($dbDSN, $dbHost, $dbName), $dbUser, $dPassword);
			// run the install script
			$dbh->exec(file_get_contents('data/install.sql'));

			// create the INI files
#[TODONOW] arrayacess on INIFile, move this to top of function
			$file = new INIFile();
			$file->setValue('db.dsn', $dbDSN);
			$file->setValue('db.host', $dbHost);
			$file->setValue('db.user', $dbUser);
			$file->setValue('db.password', $dbPassword);
			$file->setValue('db.name', $dbName);
			$file->save('quiki.ini');

			// display the main page
			$root = new RootResource();
			return $root->GET($request);
		}
		catch (PDOException $e)
		{
			// there was an error in the installation
			$doc = new SimpleXMLElement('<installation error="true" />');
			$doc->dsn = $dbDSN;
			$doc->host = $dbHost
			$doc->user = $dbUser;
			$doc->password = $dbPassword;
			$doc->name = $dbName;
			// create and send the response
			return $this->formatResponse($request, new HTTPResponse(), $doc);
		}
	}
}

?>
