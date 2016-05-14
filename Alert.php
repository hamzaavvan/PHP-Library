<?php

// This class needs bootstrap.css and glyphicon font files to be worked properly

class Alert {
	/*
	_______________________________________________

	$type = BOOTSTRAP(Refer to Documentation) alert classes (used in $message)
	$type argument can be ['success' || 'danger' || 'info' || 'blah-blah-blah']

	$alert = Your message or any kind of alert

	For those icons(glyphicons) refer to BOOTSRAP manual
	_______________________________________________
	
	*/
	public static function message($type, $alert) {
		$message = null;

		$glyphicons = array(
						'danger' => 'exclamation-sign', // show an exclamatory sign before alert (warning)
						'success' => 'ok', // You will get this automatically :p
						// Add more as you want but with reference to bootstrap classes
					   );

		if (!empty($alert)) {
			foreach ($glyphicons as $glyphicon => $value) {
				if ($type == $glyphicon) {
						$message .= "<div class='alert alert-$type' role='alert'><span class='glyphicon glyphicon-$value' aria-hidden='true'></span> $alert</div>";
						break;
				}
			}
		}
		return $message;
	}
}