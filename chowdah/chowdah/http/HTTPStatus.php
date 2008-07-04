<?php

class HTTPStatus {
	// status constants
	public const CONTINUE = 100;
	public const SWITCHING_PROTOCOLS = 101;
	public const PROCESSING = 102;
	public const OK = 200;
	public const CREATED = 201;
	public const ACCEPTED = 202;
	public const NON_AUTHORITATIVE_INFORMATION = 203;
	public const NO_CONTENT = 204;
	public const RESET_CONTENT = 205;
	public const PARTIAL_CONTENT = 206;
	public const MULTI_STATUS = 207;
	public const IM_USED = 226;
	public const MULTIPLE_CHOICES = 300;
	public const MOVED_PERMANENTLY = 301;
	public const FOUND = 302;
	public const SEE_OTHER = 303;
	public const NOT_MODIFIED = 304;
	public const USE_PROXY = 305;
	public const SWITCH_PROXY = 306;
	public const TEMPORARY_REDIRECT = 307;
	public const BAD_REQUEST = 400;
	public const UNAUTHORIZED = 401;
	public const PAYMENT_REQUIRED = 402;
	public const FORBIDDEN = 403;
	public const NOT_FOUND = 404;
	public const METHOD_NOT_ALLOWED = 405;
	public const NOT_ACCEPTABLE = 406;
	public const PROXY_AUTHENTICATION_REQUIRED = 407;
	public const REQUEST_TIMEOUT = 408;
	public const CONFLICT = 409;
	public const GONE = 410;
	public const LENGTH_REQUIRED = 411;
	public const PRECONDITION_FAILED = 412;
	public const REQUEST_ENTITY_TOO_LARGE = 413;
	public const REQUEST_URI_TOO_LONG = 414;
	public const UNSUPPORTED_MEDIA_TYPE = 415;
	public const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	public const EXPECTATION_FAILED = 417;
	public const UNPROCESSABLE_ENTITY = 422;
	public const LOCKED = 423;
	public const FAILED_DEPENDENCY = 424;
	public const UPGRADE_REQUIRED = 426;
	public const INTERNAL_SERVER_ERROR = 500;
	public const NOT_IMPLEMENTED = 501;
	public const BAD_GATEWAY = 502;
	public const SERVICE_UNAVAILABLE = 503;
	public const GATEWAY_TIMEOUT = 504;
	public const HTTP_VERSION_NOT_SUPPORTED = 505;
	public const REDIRECTION_FAILED = 506;
	public const INSUFFICIENT_STORAGE = 507;
	public const NOT_EXTENDED = 510;
	
	public static function getReasonPhrase($code) {
		return HTTPStatusCode::$reasonPhrases[$code]; 
	}

	protected static $reasonPhrases = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		226 => 'IM Used',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Switch Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		426 => 'Upgrade Required',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Redirection Failed',
		507 => 'Insufficient Storage',
		510 => 'Not Extended'
	    );
}