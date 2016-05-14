<?php

class Validate extends CRUD {
	public $session_error = false;
	public $message = '';
	public $session_type = 'session';

	public function valid($array = array()) {
		// Reversed that array for delivering proper functionality in nested loops
		$array = array_reverse($array);

		foreach ($array as $key => $value) {
			$keys = $this->split('|', $value, true);

			foreach ($keys as $value) {
				switch ($value) {
					case 'required': // check wether required field is empty
						$this->required($key);
					break;

					case 'sanitize_email': // check wether email is correct
						$this->sanitize_email($key);
					break;

					case 'sanitize_username': // check wether email is correct
						$this->sanitize_username($key);
					break;

					/*
					     _______________________________________________________________
							The argument for the following case should be in this way:
							length[operators]:[length]

							$operators = ['==', '!=', '>', '<', '>=', '<=']
							$length = ANY_INTEGER		
						 _______________________________________________________________
					*/
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
				}				
			}
		}
		return $this;
	}

	public function setError($message = '') {
			$this->session_error = true;
			$this->message = $message;
	}

	public function split($pattern, $string, $reverse = false) {
		if ($reverse == true && preg_match("/$pattern/", $string)) {
			$keys = array_reverse(explode($pattern, $string)); // exploding string with reverse order
		}else if ($reverse == false) {
			$keys = explode($pattern, $string); // Simply explode the string
		}		
		return $keys;
	}

	private function required($var) {
		if (empty(Input::get($var))) {
			$this->setError("All fields are required!");
		}
	}

	private function sanitize_email($email) {
		if (filter_var(Input::get($email), FILTER_VALIDATE_EMAIL) == false) {
			$this->setError("Incorrect email address!");
		}
	}

	private function sanitize_username($username) {
		if (preg_match('/ /', Input::get($username)) == true) {
			$this->setError("Usename must not contain any spaces!");
		}else if (filter_var(Input::get($username), FILTER_VALIDATE_EMAIL) == true) {
			$this->setError("Usename must not like email!");
		}
	}

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

	private function exists($var) {
		$exists = $this->get('users', [
										$var,
										'=',
										Input::get($var)
									  ]);

		if ($exists->_count != false) {
			$this->setError(ucfirst($var).' already exists');
		}
	}
}