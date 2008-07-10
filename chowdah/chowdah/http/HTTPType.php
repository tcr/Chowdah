<?php

/**
 * HTTP Type Base Class
 * @package chowdah.http
 */

#[TODO] better protect properties and parameters?
#[TODO] support quoted-strings in parameters

abstract class HTTPType {
	// type properties
	public $type = '';
	public $params = array();

	function __construct($type, $params = array()) {
		// set the type and parameters
		$this->type = (string) $type;
		// set the parameters
		if ($params)
			foreach ((array) $params as $key => $value)
				$this->params[$key] = (string) $value;
	}
	
	public function serialize($withParams = true) {
		// return the serialized type
		$string = $this->type;
		if ($withParams)
			foreach ($this->params as $key => $value)
				$string .= ';' . $key . '=' . $value;
		return $string;
	}
	
	public function __toString() {
		return $this->serialize();
	}

	public function match($matchType, $strict = false) {
		// get the current class
		$class = get_class($this);
		
		// if an array is passed, iterate each type
		if (is_array($matchType)) {
			foreach ($matchType as $item)
				if ($this->match($item, $strict))
					return $item;
			return false;
		}

		// match the type
		if (!($matchType instanceof $class) ||
		    !($this->type == $matchType->type || $this->type == '*' || $matchType->type != '*'))
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

	protected static function __create($class, $type, $params = array()) {
		// create and return the encoding type
		return new $class($type, $params);
	}

	protected static function __parse($class, $string) {
		// parse the type
		if (!preg_match('/^\s*(?P<type>.+?)\s*(?P<params>(;[^=]+?=[^;]+?)*)$/', $string, $components))
			return false;
		// parse the parameters
		preg_match_all('/;\s*(?P<key>[^=]+?)\s*=\s*(?P<value>[^;\s]+?)/', $components['params'], $params, PREG_PATTERN_ORDER);
		$components['params'] = @array_combine($params['key'], $params['value']);

		// create the type
		return new $class($components['type'], $components['params']);
	}

	//------------------------------------------------------------------
	// sorting functions
	//------------------------------------------------------------------

	public static function findBestMatches($accepted, $available, $sort = SORT_DESC) {
		// set up the matches array to find the best match
		$matches = array(array(), array(), array(), 'type' => array());

		// iterate through the available types
		foreach ($available as $availableType) {
			// check if this is an accepted type
			if (!($acceptedType = $availableType->match($accepted)))
				continue;

			// add the match to the array
			$matches[0][] = isset($acceptedType->params['q']) ? $acceptedType->params['q'] : 1;
			$matches[1][] = count($acceptedType->params) - isset($acceptedType->params['q']);
			$matches[2][] = $acceptedType->type != '*';
			$matches['type'][] = $availableType;
		}

		// sort and return the matches
		array_multisort(
			$matches[0], $sort,
			$matches[1], $sort,
			$matches[2], $sort,
			$matches['type'], $sort
		    );
		return $matches['type'];
	}
}

?>