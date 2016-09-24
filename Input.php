<?php

class Input {
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

	public static function get($name) {
		if (isset($_POST[$name])) {
			return $_POST[$name];
		}else if (isset($_GET[$name])) {
			return $_GET[$name];
		}
		return '';
	}
}