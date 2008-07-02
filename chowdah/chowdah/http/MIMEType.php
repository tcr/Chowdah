<?php

/**
 * MIME Type
 *
 * see: http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.1
 * @package chowdah.http
 */

class MIMEType extends HTTPType {
	public $type = '';
	public $subtype = '';
	public $params = array();

	function __construct($type, $subtype, $params = null) {
		// set the type, subtype, and parameters
		$this->type = (string) $type;
		$this->subtype = (string) $subtype;
		// validate the parameters
		if ($params)
			foreach ((array) $params as $key => $value)
				$this->params[$key] = (string) $value;
	}

	public function serialize($withParams = true) {
		// return the serialized type
		$string = $this->type . '/' . $this->subtype;
		if ($withParams)
			foreach ($this->params as $key => $value)
				$string .= ';' . $key . '=' . $value;
		return $string;
	}

	public function match($matchType, $strict = false) {
		// get the current class type
		$class = get_class($this);

		// if an array is passed, iterate each type
		if (is_array($matchType)) {
			foreach ($matchType as $item)
				if ($this->match($item, $strict))
					return $item;
			return false;
		}

		// match the type and subtype
		if (!($matchType instanceof $class) ||
		    !($this->type == $matchType->type || $this->type == '*' || $matchType->type != '*') ||
		    !($this->subtype == $matchType->subtype || $this->subtype == '*' || $matchType->subtype == '*'))
			return false;
		// match the parameters (when strict)
		if ($strict && $this->params != $matchType->params)
			return false;
		// match successful
		return true;
	}

	//------------------------------------------------------------------
	// creation functions
	//------------------------------------------------------------------

	public static function create($type, $subtype, $params = array()) {
		// create and return the mime type
		return new MIMEType($type, $subtype, $params);
	}

	public static function parse($string) {
		// parse the type
		if (!preg_match('/^\s*(?P<type>.+?)\s*\/\s*(?P<subtype>.+?)\s*(?P<params>(;[^=]+?=[^;]+?)*)$/', $string, $components))
			return false;
		// parse the parameters
		preg_match_all('/;\s*(?P<key>[^=]+?)\s*=\s*(?P<value>[^;\s]+)/', $components['params'], $params, PREG_PATTERN_ORDER);
		$components['params'] = @array_combine($params['key'], $params['value']);
		
		// create the type
		return new MIMEType($components['type'], $components['subtype'], $components['params']);
	}

	//------------------------------------------------------------------
	// sorting functions
	//------------------------------------------------------------------

	public static function findBestMatches($accepted, $available, $sort = SORT_DESC) {
		// set up the matches array to find the best match
		$matches = array(array(), array(), array(), array(), array(), 'type' => array());

		// iterate through the available type
		foreach ($available as $availableType) {
			// check if this is an accepted type
			if (!($acceptedType = $availableType->match($accepted)))
				continue;

			// add the match to the array
			$matches[0][] = isset($acceptedType->params['q']) ? $acceptedType->params['q'] : 1;
			$matches[1][] = isset($availableType->params['qs']) ? $availableType->params['qs'] : 1;
			$matches[2][] = count($acceptedType->params) - isset($acceptedType->params['q']);
			$matches[3][] = $acceptedType->type != '*';
			$matches[4][] = $acceptedType->subtype != '*';
			$matches['type'][] = $availableType;
		}

		// sort and return the matches
		array_multisort(
			$matches[0], $sort,
			$matches[1], $sort,
			$matches[2], $sort,
			$matches[3], $sort,
			$matches[4], $sort,
			$matches['type'], $sort
		    );
		return $matches['type'];
	}
}

?>