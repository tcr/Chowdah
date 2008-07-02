<?php

/**
 * Charset Type
 *
 * see: http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.2
 * @package chowdah.http
 */
 
class CharsetType extends HTTPType {
	// class overrides
	public static function create($type, $params = array()) {
		return parent::__create('CharsetType', $type, $params);
	}
	
	public static function parse($string) {
		return parent::__parse('CharsetType', $string);
	}
}

?>