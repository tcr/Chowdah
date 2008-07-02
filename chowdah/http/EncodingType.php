<?php

/**
 * Encoding Type
 *
 * see: http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.3
 * @package chowdah.http
 */

class EncodingType extends HTTPType {
	// class overrides
	public static function create($type, $params = array()) {
		return parent::__create('EncodingType', $type, $params);
	}
	
	public static function parse($string) {
		return parent::__parse('EncodingType', $string);
	}
}

?>