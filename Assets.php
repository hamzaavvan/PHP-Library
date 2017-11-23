<?php

namespace Cms\Helpers;

use \Cms\Providers\Input;

class Assets {
	public static function bootstrap(array $assets)
	{
		if (is_array($assets)&&count($assets)) {
			foreach ($assets as $file => $asset) {
				if (preg_match('/\|/', $file)) {
					$files = explode('|', $file);

					foreach ($files as $file) {
						self::set($file, $asset);
					}
				} else {
					self::set($file, $asset);
				}
			}
			
		}else {
			return false;
		}
	}

	private static function set($file, $asset)
	{
		if (self::isfile($file)) {
			if (isset($asset['css'])&&!empty($asset['css'])) {
				$css = $asset['css'];

				if (is_array($css)&&count($css)>0) self::css($css);
			}

			if (isset($asset['js'])&&!empty($asset['js'])) {
				$js = $asset['js'];

				if (is_array($js)&&count($js)>0) self::js($js);
			}
		}
	}

	public static function js(array $array) {
		if (is_array($array)&&count($array)>0) {
			foreach ($array as $js) {
				echo '<script src="'.$js.'"></script>';
			}
		}
	}

	public static function css(array $array) {
		if (is_array($array)&&count($array)>0) {
			foreach ($array as $css) {
				echo '<link rel="stylesheet" href="'.$css.'">';
			}
		}
	}

	private static function isfile(string $file) {
		return Input::get('sec')==trim($file) ? true : false;
	}
}