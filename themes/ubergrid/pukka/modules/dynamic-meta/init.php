<?php
	define('DM_URI', PUKKA_URI .'/'. PUKKA_MODULES_DIR_NAME  .'/dynamic-meta');

	include_once('include/dynamic.meta.class.php');
    include_once('include/functions.php');

	// Set on which post types should page builder be used
	$use_on = array('page');
	if( 'on' == pukka_get_option('post_page_builder') ){
		$use_on[] = 'post';
	}

	$dynamic_meta = new Dynamic_Meta($use_on); // Dynamic meta