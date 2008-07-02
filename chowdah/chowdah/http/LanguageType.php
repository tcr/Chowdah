<?php

/**
 * Language Type
 *
 * see: http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
 * @package chowdah.http
 */

class LanguageType extends HTTPType {
	// class overrides
	public static function create($type, $params = array()) {
		return parent::__create('LanguageType', $type, $params);
	}
	
	public static function parse($string) {
		return parent::__parse('LanguageType', $string);
	}
}

?>