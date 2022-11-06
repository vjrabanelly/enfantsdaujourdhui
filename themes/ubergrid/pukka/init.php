<?php
	// CONFIG
	define('PUKKA_VERSION', '0.7.2');
	define('PUKKA_THEME_VERSION', '1.2.6');
	define('PUKKA_THEME_NAME', 'UberGrid');
	
	define('PUKKA_HOMEPAGE', 'http://pukkathemes.com/');
	define('THEME_DIR', get_template_directory());
	define('THEME_URI', get_template_directory_uri());
// DIR NAMES
	define('PUKKA_OVERRIDES_DIR_NAME', 'pukka-overrides');
	define('PUKKA_MODULES_DIR_NAME', 'modules');

	define('PUKKA_DIR', THEME_DIR .'/pukka');
	define('PUKKA_URI', THEME_URI .'/pukka');
	define('PUKKA_FRAMEWORK_DIR', THEME_DIR .'/pukka/framework');
	define('PUKKA_FRAMEWORK_URI', THEME_URI .'/pukka/framework');
	define('PUKKA_OPTIONS_NAME', 'pukka_options');
	define('PUKKA_THEME_COLORSCHEME_NAME', 'pukka_theme_colorscheme');

	// CONTENT INIT
	define('PUKKA_POSTMETA_PREFIX', '_pukka_');
		
	// Functions & Hooks
	include_once(PUKKA_DIR .'/pukka_functions.php');
	
	// FRAMEWORK INIT
	include_once(PUKKA_FRAMEWORK_DIR . '/pukka_init.php');
	
	include_once(PUKKA_DIR . '/include/ubergrid.theme.class.php');
	include_once(PUKKA_DIR . '/util/init-theme-default-values.php');
	include_once(PUKKA_DIR . '/util/init-theme-styles.php');
	include_once(PUKKA_DIR . '/util/init-theme-options.php');

	// MODULES
	include_once PUKKA_DIR . '/'. PUKKA_MODULES_DIR_NAME .'/grid-layout/init.php';
	include_once PUKKA_DIR . '/'. PUKKA_MODULES_DIR_NAME .'/dynamic-meta/init.php';
	
	// Init main theme object
	$pukka = new UberGridTheme($pukka_theme_option_pages);

	include_once(PUKKA_DIR . '/util/init-post-meta.php');
	$meta_box = new PukkaMetaBox($meta_boxes);
