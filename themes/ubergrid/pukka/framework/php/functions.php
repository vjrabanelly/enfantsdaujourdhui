<?php

	/**
	 * Returns options name depending on the currently active site language.
	 * Used for compatibility with WPML plugin.
	 *
	 * @since Pukka 1.0
	 *
	 * @return string Options key
	 */
	function pukka_get_options_name(){
		$opt_sufix = '';

		// set options name suffix if WPML is activated
		if(defined('ICL_LANGUAGE_CODE')){
			global $sitepress;
			$default_lng = $sitepress->get_default_language();
			if($default_lng != ICL_LANGUAGE_CODE){
				$opt_sufix .= '_' . ICL_LANGUAGE_CODE;
			}
		}
		return PUKKA_OPTIONS_NAME . $opt_sufix;
	}


	/**
	 * Returns theme option by option name.
	 *
	 * @since Pukka 1.0
	 *
	 * @param string $key Option name
	 * @return mixed
	 */
	function pukka_get_option($key){
		$options_name = pukka_get_options_name();
		$values = get_option($options_name);
		
		return (!empty($values[$key])) ? $values[$key] : pukka_get_option_default($key);
	}
	
	/**
	 * Returns theme option by option name.
	 *
	 * @since Pukka 1.0.7
	 *
	 * @param string $key Option name
	 * @return mixed
	 */
	function pukka_get_option_default($key){
		global $pukka_theme_option_pages;
		global $pukka_options_list;
		
		if(empty($pukka_options_list)){
			foreach($pukka_theme_option_pages['pukka_theme_settings_page']['tabs'] as $tab){
				foreach($tab['fields'] as $field){
					if(!empty($field['default']) && !empty($field['id'])){
						$pukka_options_list[$field['id']] = $field['default'];
					}
				}
			}
		}
		
		if(!empty($pukka_options_list[$key])){
			return $pukka_options_list[$key];
		}
		
		return '';
	}

	/**
	 * Set single option in theme settings
	 *
	 * @since Pukka 1.0
	 *
	 * @param string $key Option name
	 * @param mixed $value Option value
	 */
	function pukka_set_option($key, $value){

		if (trim($key) == '')
				return;

		$options_name = pukka_get_options_name();
		$pukka_values = get_option($options_name);
		$pukka_values[$key] = $value;

		update_option($options_name, $pukka_values);
	}

	/* BEGIN: Pukka hook section ***************************************/

	function pukka_after_content(){
		global $post;
		do_action('pukka_after_content', $post->ID);
	}

	function pukka_after_body(){
		global $post;

		// search with no results
		if( isset($post) && is_object($post) ){
			do_action('pukka_after_body', $post->ID);
		}
	}

	/* END: Pukka hook section ***************************************/

	/*
	* Translates passed string using translation in .mo file
	* Used to translate theme settings page
	*/
	function pukka_translate($text, $domain = 'default') {
		global $l10n;

		if (isset($l10n[$domain]))
			return apply_filters('gettext', $l10n[$domain]->translate($text), $text, $domain);
		else
			return $text;
	}

	add_filter('pukka_translate_theme_settings', 'pukka_translate_theme_option_pages');
	function pukka_translate_theme_option_pages($theme_option_pages){

		// translate theme options
		foreach( $theme_option_pages as &$page ){
				$page['page_title'] = isset($page['page_title']) ? pukka_translate($page['page_title'], 'pukka') : '';
				$page['menu_title'] = isset($page['menu_title']) ? pukka_translate($page['menu_title'], 'pukka') : '';
				$page['page_description'] = isset($page['page_description']) ? pukka_translate($page['page_description'], 'pukka') : '';

			foreach( $page['tabs'] as &$tab ){
				$tab['title'] = pukka_translate($tab['title'], 'pukka');
				foreach( $tab['fields'] as &$field ){
					$field['title'] = isset($field['title']) ? pukka_translate($field['title'], 'pukka') : '';
					$field['desc'] = isset($field['desc']) ? pukka_translate($field['desc'], 'pukka') : '';
				}
			}
		}

		return $theme_option_pages;
	}

	/**
	 * Gets all theme option fields and its settings
	 *
	 * @return array
	 */
	function pukka_get_theme_options(){
		$options_name = pukka_get_options_name();
		// get all theme options
		$options = get_option($options_name);
		// get options page
		global $pukka_theme_option_pages;
		$options_page = $pukka_theme_option_pages['pukka_theme_settings_page'];

		$res = array();
		// iterate trough all tabs on page
		foreach ($options_page['tabs'] as $tab) {
			// and trough all fields on each tab
			foreach ($tab['fields'] as $field) {
				//we check if field is in $options array
				$tmp = array(); // this temp array will containt data about one field (id, values etc...)
								// that will be returned
				// if field doesn't have id, it does not store values in db, so just skip it
				if(!isset($field['id'])) continue;
				if(array_key_exists($field['id'], $options)){
					// save id to response
					$tmp['id'] = $field['id'];
					$tmp['value'] = $options[$field['id']];
					$tmp['type'] = $field['type'];

					// if $field type is 'file' then it is one of the images in
					// theme settings, so besides returning value (which is image id)
					// we also need to pass url for the image preview,
					// for everything else, we are good to go
					if('file' == $field['type']){
						$file = wp_get_attachment_image_src($tmp['value'], 'full');
						if($file){
							$tmp['url'] = $file[0];
						}else{
							$tmp['url'] = '';
						}
					}
					$res[] = $tmp;
				}
			}
		}

		return $res;
	}

	/* BEGIN: Dynamic meta section ***************************************/

	function pukka_get_dm_html($data){
		global $dynamic_meta;
		$out = $dynamic_meta->getDMHTML($data);

		return $out;
	}

	function pukka_get_dm_html_by_id($post_id, $echo = true){
		$meta = get_post_meta($post_id, '_pukka_dynamic_meta_box', true);
		return pukka_get_dm_html($meta);
	}

	function pukka_after_content_dynamic_meta($post_id){
		$meta = get_post_meta($post_id, '_pukka_dynamic_meta_box', true);
		if(!empty($meta)){
			echo '<div class="dm-wrap clearfix">' . pukka_get_dm_html($meta) . '</div>';
		}
	}
	add_action('pukka_after_content', 'pukka_after_content_dynamic_meta', 1, 1);
	/* END: Dynamic meta section ***************************************/


	/**
	 * Converting string to url safe string for slugs
	 *
	 * @since Pukka 1.0
	 */
	function pukka_slugify($text) {
		$table = array(
			"Š" => "S", "š" => "s", "Đ" => "Dj", "đ" => "dj", "Ž" => "Z", "ž" => "z", "Č" => "C", "č" => "c", "Ć" => "C", "ć" => "c",
			"À" => "A", "Á" => "A", "Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A", "Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E",
			"Ê" => "E", "Ë" => "E", "Ì" => "I", "Í" => "I", "Î" => "I", "Ï" => "I", "Ñ" => "N", "Ò" => "O", "Ó" => "O", "Ô" => "O",
			"Õ" => "O", "Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U", "Û" => "U", "Ü" => "U", "Ý" => "Y", "Þ" => "B", "ß" => "Ss",
			"à" => "a", "á" => "a", "â" => "a", "ã" => "a", "ä" => "a", "å" => "a", "æ" => "a", "ç" => "c", "è" => "e", "é" => "e",
			"ê" => "e", "ë" => "e", "ì" => "i", "í" => "i", "î" => "i", "ï" => "i", "ð" => "o", "ñ" => "n", "ò" => "o", "ó" => "o",
			"ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o", "ù" => "u", "ú" => "u", "û" => "u", "ý" => "y", "ý" => "y", "þ" => "b",
			"ÿ" => "y", "Ŕ" => "R", "ŕ" => "r", "/" => "-", " " => "-"
		);

		// Remove duplicated spaces
		$text = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $text);

		// Returns the slug
		return strtolower(strtr($text, $table));
	}

	/**
	 * Returns fields required for contact forms spam check
	 *
	 * @since Pukka 1.1.1
	 *
	 * @return mixed
	 */
	function pukka_get_spam_fields(){
		$out = '';
		$numbers = explode(',', pukka_get_option('form_spam_numbers'));

		for($i=1; $i<=10; $i++ ){
			if(in_array($i, $numbers))
				$out .= '<input type="hidden" name="'. $i .'" value="'. $i .'" />' ."\n";
			else
				 $out .= '<input type="hidden" name="'. $i .'" value="" />' ."\n";
		}

		return $out;
	}

	/**
	 * Gets all registered thumbnail sizes
	 *
	 * @return array $sizes registered size, width x height
	 * http://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
	 */
	function pukka_get_thumbnail_sizes(){
	    global $_wp_additional_image_sizes;
		$sizes = array();
		foreach( get_intermediate_image_sizes() as $s ){
			$sizes[ $s ] = array( 0, 0 );
			if( in_array( $s, array( 'thumbnail', 'medium', 'large' ) ) ){
				$sizes[ $s ][0] = get_option( $s . '_size_w' );
				$sizes[ $s ][1] = get_option( $s . '_size_h' );
			}else{
				if( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[ $s ] ) )
					$sizes[ $s ] = array( $_wp_additional_image_sizes[ $s ]['width'], $_wp_additional_image_sizes[ $s ]['height'], );
			}
		}

		return $sizes;
	}