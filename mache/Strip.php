<?php

class Strip {
	protected $id;
	protected $title;
	protected $content;
	protected $tags;
	
	// static construction database query
	private static $constructQuery = null;

	function __construct($id) {
		// generate request if it doesn't exist
		if (!Strip::$constructQuery) {
			$dbh = Mache::getDBConnection();
			Strip::$constructQuery = $dbh->prepare('SELECT * FROM `strips` WHERE `id` = ?');
		}
		
		// get data for this object
		Strip::$constructQuery->execute(array($id));
		$data = Strip::$constructQuery->fetch();
		Strip::$constructQuery->closeCursor();

		// if no data was found, throw Exception
		if (!$data)
			throw new Exception('No Strip with the id #' . $id . ' was found.');		
		// save the properties
		foreach ($data as $key => $value)
			$this->$key = $value;
		// explode tags
		$this->tags = preg_split('/\s+/', $this->tags, null, PREG_SPLIT_NO_EMPTY);
	}
	
	function __get($key) {
		// return protected values
		if (isset($this->$key))
			return $this->$key;
		return false;
	}
	
	public function update($title = null, $content = null, $tags = null) {
		// get parameters
		if ($title !== null)
			$this->title = $title;
		if ($content !== null)
			$this->content = $content;
		if ($tags !== null)
			$this->tags = (array) $tags;
		
		// update database
		$dbh = Mache::getDBConnection();
		$sth = $dbh->prepare('UPDATE `strips` SET `title` = ?, `content` = ?, `tags` = ? WHERE `id` = ?');
		$sth->execute(array($title, $content, implode(' ', $tags), $this->id));
	}
	
	public function delete() {
		// delete this strip
		$dbh = Mache::getDBConnection();
		$sth = $dbh->prepare('DELETE FROM `strips` WHERE `id` = ? LIMIT 1');
		return (bool) $sth->execute(array($this->id));
	}
	
	static public function parseMarkup($content, $document) {
		// normalize arguments
		$document = $document[0];
		
		// textile string
		$textile = new Textile();
		$content = $textile->TextileThis($content);
		// parse Mache links
		$content = preg_replace_callback('/\[\[([0-9a-f]{6})(\s+[^\]]+)?\]\]/', array('Strip', 'parseMacheLink'), $content);
		// parse XTML
		$xhtml = new DOMDocument();
		$xhtml->loadXML('<content>' . $content . '</content>');
		
		// import XHTML into document
		return $document->importNode($xhtml->documentElement, true);
	}
	
	static public function parseMacheLink($matches) {
		// insert link
		try {
			// get the strip
			$strip = new Strip($matches[1]);
			
			// return link
			return '<a href="/strips/' . $matches[1] . '">' .
			    (strlen($matches[2]) ? $matches[2] : $strip->title) . '</a>';
		} catch (Exception $e) {
			// strip doesn't exist, return raw string
			return $matches[0];
		}
	}
	
	//----------------------------------------------------------------------
	// static functions
	//----------------------------------------------------------------------
	
	static public function create($title = null, $content = null, $tags = null) {
		// submit query
		$dbh = Mache::getDBConnection();
		$sth = $dbh->prepare('INSERT INTO `strips` (`id`, `title`, `content`, `tags`) VALUES (?, ?, ?, ?)');
		while (!$sth->execute(array($id = substr(md5(time()), 0, 6), $title, $content, implode(' ', $tags))));

		// return new strip
		return new Strip($id);
	}
	
	static public function getList($search = array()) {
		// create where clause
		if ($search['title']) {
			$where[] = "`title` LIKE CONCAT('%', ? ,'%')";
			$whereArgs[] = $search['title'];
		}
		if (count($search['tags'])) {
			foreach ($search['tags'] as $tag) {
				$where[] = "`tags` LIKE CONCAT('%', ? ,'%')";
				$whereArgs[] = $tag;
			}
		}
		if ($search['content']) {
			$where[] = "`content` LIKE CONCAT('%', ? ,'%')";
			$whereArgs[] = $search['content'];
		}

		// query for id list
		$dbh = Mache::getDBConnection();
		$sth = $dbh->prepare('SELECT id FROM `strips` ' .
		    (count($where) ? 'WHERE' . implode(' AND ', $where) : '') . 'ORDER BY id ASC');
		$sth->execute($whereArgs);
		$data = $sth->fetchAll();
		
		// create strip objects
		$strips = array();
		foreach ($data as $strip)
			if (strlen($strip['id']))
				$strips[] = new Strip($strip['id']);
		return $strips;
	}
}

?>