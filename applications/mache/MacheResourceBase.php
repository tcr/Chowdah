<?php

abstract class MacheResourceBase extends HTTPResourceBase {
	//----------------------------------------------------------------------
	// representations
	//----------------------------------------------------------------------
	
	protected function formatResponse(HTTPRequest $request, HTTPResponse $response, $doc) {
		// convert simplexml to doc
		if ($doc instanceof SimpleXMLElement)
			$doc = dom_import_simplexml($doc)->ownerDocument;
	
		// create the XSLT processor
		$xsl = new XSLTProcessor();
		$stylesheet = new DOMDocument();
		// load the template
		$stylesheet->load('templates/' . get_class($this) . '.html.xsl');
		$xsl->importStyleSheet($stylesheet);
		$xsl->registerPHPFunctions();

		// set path
		$xsl->setParameter(null, 'path', $request->getURL()->path);
	
		// transform the document
		$response->setParsedContent($xsl->transformToDoc($doc), new MIMEType('text', 'html'));
		return $response;
	}
}

?>