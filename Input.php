<?php

namespace App;

class Input {
	/**
	 * Must take @bool to proceed
	 * refer to getMagicQuotes() method
	 *
	 * @var boolean
	 */
	private static $magic_quotes_exists;


	public static function exists($action = 'post') {
		switch ($action) {
			case 'post':
				return (!empty($_POST)) ? true : false;
			break;

			case 'get':
				return (!empty($_GET)) ? true : false;
			break;

			default:
				return false;
		}

	}


	public static function get($name, $purify = false) {

		if (isset($_POST[$name])) {
			$var = $_POST[$name];

			return self::verify($var, $purify);
		}else if (isset($_GET[$name])) {
			$var = $_GET[$name];

			return self::verify($var, $purify);
		}

		return false;
	}


	public static function verify($input, $purify = false) {
		$input = is_array($input) ? array_map('self::verify', $input) : static::sanitize($input, $purify);

		return $input;
	}

	public static function check($name, $in = '') {
		$in = strtolower($in);

		switch ($in) {
			case 'get':
				if (isset($_GET[$name])) {
					return true;
				}
			break;

			case 'post':
				if (isset($_POST[$name])) {
					return true;
				}
			break;
			
			default:
				if (isset($_POST[$name])) {
					return true;
				}else if (isset($_GET[$name])) {
					return true;
				}
		}

		return false;
	}


	public function sanitize($string, $purify = false) 
	{
		$string = trim($string);

		if ($purify==true)
			$string = stripslashes(strip_tags($string));

		$string = htmlentities($string, ENT_QUOTES, "UTF-8");

		return $string;
	}

	public function getMagicQuotes() {
		if(function_exists('get_magic_quotes_gpc')) {
			self::$magic_quotes_exists = true;
			return self::$magic_quotes_exists;
		}
	}

	/**
	 * Convert post request into url param string
	 *
	 * @param array $stream
	 * @param string $initParam
	 * @return string $params
	 */
	public function splitStream($stream, $initParam = '') {
		$params = !empty($initParam) ? $initParam : '';
		$magicQuotesExist = self::getMagicQuotes();

		foreach ($stream as $key => $value) {
			if ($magicQuotesExist == true && get_magic_quotes_gpc() == 1) {
				$value = urlencode(stripslashes($value));
			}else {
				$value = urlencode($value);
			}

			$params .= !empty($params) ? '&' : '';
			$params .= "$key=$value";
		}

		return $params;
	}

}
