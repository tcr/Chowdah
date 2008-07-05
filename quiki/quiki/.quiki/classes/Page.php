<?php

class Page {
	protected $title;
	protected $content;
	protected $tags;
	
	function __construct($title)
	{
		// initialize database handlers
		Page::init();
		
		// get data for this object
		Page::$readQuery->execute(array($title));
		if (!($data = Page::$readQuery->fetch()))
			throw new Exception('No page with the title "' . $title . '" was found.');
		Page::$readQuery->closeCursor();
		
		// save the properties
		foreach ($data as $key => $value)
			$this->$key = $value;
		// explode tags array
		$this->tags = preg_split('/\s+/', $this->tags, null, PREG_SPLIT_NO_EMPTY);
	}
	
	public function update($title = null, $content = null, $tags = null)
	{
		// get database title
		$dbTitle = $this->title;
		
		// get parameters
		if ($title !== null)
			$this->title = $title;
		if ($content !== null)
			$this->content = $content;
		if ($tags !== null)
			$this->tags = (array) $tags;
		
		// update database
		Page::$updateQuery->execute(array($this->title, $this->content, implode(' ', $this->tags), $dbTitle));
	}
	
	public function delete()
	{
		// delete this object
		return (bool) Page::$deleteQuery->execute(array($this->title));
	}
	
	function __get($key)
	{
		// return protected values
		if (isset($this->$key))
			return $this->$key;
		return false;
	}
	
	//----------------------------------------------------------------------
	// static functions
	//----------------------------------------------------------------------
	
	static public function create($title, $content = null, $tags = null)
	{
		// initialize database handlers
		Page::init();
		
		// create new page
		Page::$createQuery->execute(array($title, $content, implode(' ', $tags)));
		return new Page($title);
	}
	
	static public function getList($search = array(), $start = 0, $limit = 999999)
	{
		// initialize database handlers
		Page::init();
		
		// create where clause
		$bindParams = array();
		if ($search['title']) {
			$where[] = "`title` LIKE CONCAT('%', ? ,'%')";
			$bindParams[] = $search['title'];
		}
		if (count($search['tags'])) {
			foreach ($search['tags'] as $tag) {
				$where[] = "`tags` LIKE CONCAT('%', ? ,'%')";
				$bindParams[] = $tag;
			}
		}
		if ($search['content']) {
			$where[] = "`content` LIKE CONCAT('%', ? ,'%')";
			$bindParams[] = $search['content'];
		}

		// generate unique query
		$query = Page::$dbh->prepare('SELECT `title` FROM `pages` ' .
		    (count($where) ? 'WHERE' . implode(' AND ', $where) : '') . 'ORDER BY `title` ASC LIMIT ?, ?');
		// bind parameters
		for ($i = 0; $i < count($bindParams); $i++)
			$query->bindValue($i + 1, $bindParams[$i]);
		$query->bindValue($i++ + 1, $start, PDO::PARAM_INT);
		$query->bindValue($i++ + 1, $limit, PDO::PARAM_INT);
		
		// create page objects
		$pages = array();
		$query->execute();
		foreach ($query->fetchAll() as $page)
			$pages[] = new Page($page['title']);
		return $pages;
	}
	
	static public function parseMarkup($content, $document) {
		// normalize arguments
		$document = $document[0];
		
		// use textile to format content
		$textile = new Textile();
		$content = $textile->TextileThis($content);
		// parse XHTML
		$xhtml = new DOMDocument();
		$xhtml->loadXML('<content>' . $content . '</content>');
		
		// import XHTML into document
		return $document->importNode($xhtml->documentElement, true);
	}
	
	protected static $dbh;
	protected static $createQuery;
	protected static $readQuery;
	protected static $updateQuery;
	protected static $deleteQuery;
	
	static public function init() {
		// initialized flag
		static $init = false;
		if ($init)
			return;
			
		// initialize database handlers
		Page::$dbh = Quiki::getDBConnection();
		Page::$createQuery = Page::$dbh->prepare('INSERT INTO `pages` (`title`, `content`, `tags`) VALUES (?, ?, ?)');
		Page::$readQuery = Page::$dbh->prepare('SELECT * FROM `pages` WHERE `title` = ?');
		Page::$updateQuery = Page::$dbh->prepare('UPDATE `pages` SET `title` = ?, `content` = ?, `tags` = ? WHERE `title` = ?');
		Page::$deleteQuery = Page::$dbh->prepare('DELETE FROM `pages` WHERE `title` = ? LIMIT 1');
		
		// set init flag
		return ($init = true);
	}
}

?>