<?php

class XSLTemplate extends XSLTProcessor
{
	protected $stylesheet;

	function __construct($template = null)
	{
		// create stylesheet
		$this->stylesheet = new DOMDocument();
		// initialize processor
		$this->registerPHPFunctions();
	
		// load template
		if ($template)
			$this->load($template);
	}
	
	public function load($template)
	{
		return $this->stylesheet->load($template) &&
		    $this->importStylesheet($this->stylesheet);
	}

	public function format($doc)
	{
		// convert simplexml to doc
		if ($doc instanceof SimpleXMLElement)
			$doc = dom_import_simplexml($doc)->ownerDocument;
		
		// return transformed document
		$file = new VirtualDocument();
		$file->setContent($this->transformToXml($doc));
		$file->setContentType(new MIMEType('text', 'html'));
		return $file;
	}
}

?>