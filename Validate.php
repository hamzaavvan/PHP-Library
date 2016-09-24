<?php

require_once 'Validationbag.php';

class Validate {
	/** To use the methods of ValidationBag trait */
	use ValidationBag;

	/**
	 * to examine the flow errors, this property will be set to true if there will be any error caught by @ValidationBag trait
	 *
	 * @var bool $session_error
	 */
	public  $session_error = false;

	/**
	 * this property will be populated with simple string when any error catched caught by @ValidationBag trait
	 *
	 * @var string $message
	 */
	public $message = '';

	/**
	 * this property is totally dependant on checksBag method provided by @ValidationBag
	 *
	 * @param string $session_type
	 */
	public $session_type = 'session';

	/**
	 * 
	 */
	public $address;

	/**
	 * Data stored when a form submits 
	 *
	 * @var array $userData
	 */
	private $userData;

	/**
	 * Info to be ignore 
	 *
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

		self::$fieldsCount = count($array);

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

						$this->checkLength($key, $operator, $length);
					break;

					case (preg_match('/^checked/', $value) == 1) ? $value : '' :
						$keys = $this->split(':', $value);						
						$action = $keys[1];

						$this->checksBag($action, $key);
					break;

					case 'exists':
						$this->exists($key);
					break;

					case 'csrf':
						if (!Token::check(Input::get($key))) {
							$this->throwError();
						}
					break;
				}				
			}
		}
		return $this;
	}

	public function userData() {
		if ($this->valErr() == false) {
			$keys = array_keys($this->userData);
			$this->userData = [];

			array_walk($keys, function ($keys) {
				$values = trim(Input::get($keys));

				if (!in_array($keys, $this->ignorable)) {
					$this->userData[$keys] = ($keys == "password") ? crc32($values) : $values;
				}
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
}
