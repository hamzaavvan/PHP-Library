<?php

require_once 'Input.php';

trait ValidationBag {
	/**
	 * instance of maximum number of fields
	 *
	 * @param int $fieldsCount
	 */
	public static $fieldsCount;

	public static function fieldsCount() {
		return static::$fieldsCount;
	}

	/**
	 * to set default error and can be usable for setting your own custom errors
	 *
	 * @param string | array $message
	 * @return void
	 */
	public function throwError(string $message = '') {
			$this->session_error = true;
			$this->message = $message;
	}

	/**
	 * to check if there is an error
	 *
	 * @return bool $session_error
	 */
	public function isError() {
		return $this->session_error;
	}


	/**
	 * to get the value set by throwError() method
	 *
	 * @return bool $session_error
	 */
	public function error() {
		if (Input::exists()) {
			return $this->message;
		}
	}

	/**
	 * set the supplied field as mandatory
	 *
	 * @param $var
	 * @return void
	 */
	protected function required($var) {
		if (empty(Input::get($var))) {
			if (self::fieldsCount() > 1) {
				$this->throwError("All fields are required!");
			}else {
				$this->throwError(ucfirst($var)." field is required!");
			}
		}
	}

	/**
	 * sanitize the user supplied data
	 *
	 * @param string $key
	 * @return void
	 */
	protected function sanitize($key) {
		switch ($key) {
			case 'username':
				if (preg_match('/ /', Input::get($key)) == true) {
					$this->throwError("Usename must not contain any spaces!");
				}else if (filter_var(Input::get($key), FILTER_VALIDATE_EMAIL) == true) {
					$this->throwError("Usename must not be like email!");
				}
			break;

			case 'email':
				if (filter_var(Input::get($key), FILTER_VALIDATE_EMAIL) == false)
				{
					$this->throwError("Incorrect email address!");
				}				
			break;
		}
	}

	/**
	 * for checking the length of passed $variable along with an $operator and limited $length
	 * The argument for the following case should be in this way:
	 * length[operators]:[length]
	 *
	 * @param string $var
	 * @param array $operators = ['==', '!=', '>', '<', '>=', '<=']
	 * @param bool $length
	 */
	protected function checkLength($var, $operator, $length) {
		$name = ucfirst($var);
		switch ($operator) {			
			case '==':
				if (strlen(Input::get($var)) != $length) {
					$this->throwError($name.' must be equal to '.$length.' characters');
				}
			break;
			case '!=':
				if (strlen(Input::get($var)) == $length) {
					$this->throwError($name.' must not be equal to '.$length.' characters');
				}
			break;
			case '<':
				if (strlen(Input::get($var)) > $length) {
					$this->throwError($name.' must be less than '.$length.' characters');
				}
			break;
			case '>':
				if (strlen(Input::get($var)) < $length) {
					$this->throwError($name.' must be greater than '.$length.' characters');
				}
			break;
			case '<=':
				if (strlen(Input::get($var)) > $length) {
					$this->throwError($name.' must be maximum of '.$length.' characters');
				}
			break;
			case '>=':
				if (strlen(Input::get($var)) < $length) {
					$this->throwError($name.' must be minimum of '.$length.' characters');
				}
			break;
			default:
					die('Oops! something went wrong, please check your conditions');
		}
	}

	/**
	 * to check if the follolwing case is true, default cases are check and remember for setting a setting
	 *
	 * @param string $action
	 * @param string $name
	 */
	private function checksBag($action = 'check', $name) {
		switch($action) {
			case 'check':
				echo (Input::get($name)) ? Input::get($name) : 'off';				
			break;
			case 'remember':
				if (Input::get($name) == "on") {
					$this->session_type = "cookies";
				}
			break;
		}
	}

	/**
	 * default exist system to check whether username/email exists, this can be extendable and is totally dependant
	 *
	 * @param string $var
	 * @return void
	 */
	private function exists($var) {
		$exists = $this->get('users', [
										$var,
										'=',
										Input::get($var)
									  ]);

		$this->address = (function () {
					$filename = explode('/', $_SERVER['SCRIPT_FILENAME']);
					$address = end($filename);
					$address = substr($address, 0, stripos($address, '.'));
		
					return $address;
				});
		
		switch ($this->address()) {
			case 'signup':
				if ($exists->_count == true) {
					$this->throwError(ucfirst($var).' already exists');
				}
			break;

			case 'signin':
				if ($exists->_count == false) {
					$this->throwError("No user exists! Have you registered ?");
				}
			break;
		}
	}
}

?>
