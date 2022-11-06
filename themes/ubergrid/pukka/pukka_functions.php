<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');

	
	function pukka_body_classes($classes) {
		$style = pukka_get_option(PUKKA_THEME_COLORSCHEME_NAME);
		if(!empty($style)){
			$classes[] = 'style-' . $style;
		}

		return $classes;
	}

	add_filter('body_class', 'pukka_body_classes');