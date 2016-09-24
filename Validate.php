<?php

class Validate extends CRUD {
	public  $session_error = false,
			$message = '',
			$session_type = 'session',
			$address;

	/**
	 * Data stored when a form submits 
	 * @var array $userData
	 */
	private $userData;

	/**
	 * Info to be ignore 
	 * @var array $ignorable
	 */
	private $ignorable = ["token"];

	/**
	 * To check for validity of passed array variables
	 *
	 * @param array $array
	 * @return array $data
	 */
	public function valid(array $array = array()) {
		$this->userData = $array;

		// Reversed that array for delivering proper functionality in nested loops
		$array = array_reverse($array);

		foreach ($array as $key => $value) {
			$keys = $this->split('|', $value, true);

			foreach ($keys as $value) {
				switch ($value) {
					case 'required': // check wether required fields are empty
						$this->required($key);
					break;

					case 'sanitize': // will sanitize specified field
						$this->sanitize($key);
					break;					
				   
					case (preg_match('/^length/', $value) == 1) ? $value : '' : // check for given length
						$keys = $this->split(':', $value);
						
						$length = $keys[1];
						$operator = $this->split('length', $keys[0])[1];

						$this->check_length($key, $operator, $length);
					break;

					case (preg_match('/^checked/', $value) == 1) ? $value : '' :
						$keys = $this->split(':', $value);						
						$action = $keys[1];

						$this->checkbox($action, $key);
					break;

					case 'exists':
						$this->exists($key);
					break;

					case 'csrf':
						if (!Token::check(Input::get($key))) {
							$this->setError();
						}
					break;
				}				
			}
		}
		return $this;
	}

	/**
	 * to set default error
	 *
	 * @param string | array $message
	 * @return void
	 */
	public function setError($message = '') {
			$this->session_error = true;
			$this->message = $message;
	}

	/**
	 * to get the value set by setError() method
	 *
	 * @return bool $session_error
	 */
	public function valErr() {
		return $this->session_error;
	}

	public function userData() {
		if ($this->valErr() == false) {
			$keys = array_keys($this->userData);
			$this->userData = [];

			array_walk($keys, function ($keys) {
				$values = trim(Input::get($keys));

				if (!in_array($keys, $this->ignorable)) {
					$this->userData[$keys] = ($keys == "password") ? crc32($values) : $values;
			});

			return $this->userData;
		}
	}

	/**
	 * usable for splitting a string and explode it with given delimeter
	 *
	 * @param string $deliimeter
	 * @param string $value
	 * @param bool $reverse
	 * @return array $key
	 */
	public function split($delimeter, $value, $reverse = false) {
		if ($reverse == true && preg_match("/$delimeter/", $value)) {
			$keys = array_reverse(explode($delimeter, $value)); // exploding string with reverse order
		}else if ($reverse == false) {
			$keys = explode($delimeter, $value); // Simply explode the string
		}		
		return $keys;
	}

	/**
	 * set the supplied field as mandatory
	 *
	 * @param $var
	 * @return void
	 */
	private function required($var) {
		if (empty(Input::get($var))) {
			$this->setError("All fields are required!");
		}
	}

	/**
	 * sanitize the user supplied data
	 *
	 * @param string $key
	 * @return void
	 */
	private function sanitize($key) {
		switch ($key) {
			case 'username':
				if (preg_match('/ /', Input::get($key)) == true) {
					$this->setError("Usename must not contain any spaces!");
				}else if (filter_var(Input::get($key), FILTER_VALIDATE_EMAIL) == true) {
					$this->setError("Usename must not be like email!");
				}
			break;

			case 'email':
				if (filter_var(Input::get($key), FILTER_VALIDATE_EMAIL) == false)
				{
					$this->setError("Incorrect email address!");
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
	private function check_length($var, $operator, $length) {
		$name = ucfirst($var);
		switch ($operator) {			
			case '==':
				if (strlen(Input::get($var)) != $length) {
					$this->setError($name.' must be equal to '.$length.' characters');
				}
			break;
			case '!=':
				if (strlen(Input::get($var)) == $length) {
					$this->setError($name.' must not be equal to '.$length.' characters');
				}
			break;
			case '<':
				if (strlen(Input::get($var)) > $length) {
					$this->setError($name.' must be less than '.$length.' characters');
				}
			break;
			case '>':
				if (strlen(Input::get($var)) < $length) {
					$this->setError($name.' must be greater than '.$length.' characters');
				}
			break;
			case '<=':
				if (strlen(Input::get($var)) >= $length) {
					$this->setError($name.' must be maximum of '.$length.' characters');
				}
			break;
			case '>=':
				if (strlen(Input::get($var)) <= $length) {
					$this->setError($name.' must be minimum of '.$length.' characters');
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
	private function checkbox($action = 'check', $name) {
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

		$this->address = address();
		
		switch ($this->address) {
			case 'signup':
				if ($exists->_count == true) {
					$this->setError(ucfirst($var).' already exists');
				}
			break;

			case 'signin':
				if ($exists->_count == false) {
					$this->setError("No user exists! Have you registered ?");
				}
			break;
		}
	}
}
