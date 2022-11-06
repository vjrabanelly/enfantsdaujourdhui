<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');
/*
* Base theme class
*
* Requires html.helper.class.php
*/

if( !class_exists('PukkaTheme') ) :

	class PukkaTheme {
		private $theme_option_pages = array(); // Holds all theme pages and options for each page
		private $menu_pos = 77.7; // Position for custom menu section
		private $menu_hooknames; // array which holds menu page hook names
		private $html; // html helper object

		// Variables that hold data which goes into <head> section
		protected $meta = '';
		protected $css = '';
		protected $js = '';

		// Meta description length
		protected $meta_desc_length = 155;

		/**
		 * Class constructor
		 *
		 * @since Pukka 1.0
		 *
		 * @param array $theme_option_pages Theme options pages and options for each page
		 */
		public function __construct($theme_option_pages) {

			$this->theme_option_pages = $theme_option_pages;

			if (is_admin()) {
				add_action('wp_ajax_pukka_framework_save', array($this, 'saveMenuPage'));
				add_action('wp_ajax_pukka_framework_reset', array($this, 'resetMenuPage'));

				add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'), 0);

				//add_action('enqueue_scripts', array($this, 'enqueueScripts'), 0);
			} else {
				// fetch google fonts for frontend
				$this->fetchGoogleFonts();
				$this->addThemeStyle();
			}

			// Create menu items
			add_action('admin_menu', array($this, 'addMenuPages'), 0);
			
			// Print Analytics code
			add_action('wp_head', array(&$this, 'printAnalytics'), 9);

			// Enqueue frontend scripts
			add_action('wp_head', array(&$this, 'enqueueScripts'), 10);

			// Generate theme's necessary meta data
			add_action('wp_head', array(&$this, 'metaData'), 9);
			// Print: custom css, js, meta data, favicon...
			add_action('wp_head', array(&$this, 'printPukkaHead'), 10);

			// Print necessary debug info
			add_action('wp_footer', array(&$this, 'printPukkaInfo'), 10);

			// Retina support
			add_filter('wp_generate_attachment_metadata', array(&$this, 'retinaSupportAttachmentMeta'), 10, 2);
			add_filter('delete_attachment', array(&$this, 'deleteRetinaSupportImages'));

			$this->html = new HtmlHelper();
			$this->html->setContext('theme_option');
		}

		/**
		 * Fetches options page data
		 *
		 * @param string $page_slug Options page slug
		 * @return mixed
		 */
		protected function getMenuPage($page_slug) {
			return $this->theme_option_pages[$page_slug];
		}

		/**
		 * Adding javascript and style
		 *
		 * @param string $hook
		 */
		public function enqueueAdminScripts($hook) {

			// Register all scripts theme is going to use (we're enqueuing some of them on pages added somewhere else: ie featured content class)
			// main admin js
			wp_register_script('pukka-admin', PUKKA_FRAMEWORK_URI . '/js/jquery.admin.js', array('jquery', 'pukka-selectbox', 'pukka-checkbox'));

			// custom selectbox
			wp_register_script('pukka-selectbox', PUKKA_FRAMEWORK_URI . '/js/jquery.selectbox-0.2.min.js', array('jquery'));

			// custom checkbox
			wp_register_script('pukka-checkbox', PUKKA_FRAMEWORK_URI . '/js/jquery.icheck.min.js', array('jquery'));

			// font awesome picker
			wp_register_script('pukka-fa-picker', PUKKA_FRAMEWORK_URI . '/js/jquery.fa.picker.js', array('jquery-ui-tabs', 'thickbox'));

			// dynamic inputs
			wp_register_script('pukka-dynamic-input', PUKKA_FRAMEWORK_URI . '/js/jquery.dynamic.input.js', array('jquery'));
		

			// main admin css
			wp_register_style('pukka-css', PUKKA_FRAMEWORK_URI . '/css/admin.css');

			// font awesome
			wp_register_style('font-awesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css');

			if (!array_key_exists($hook, $this->menu_hooknames)) {
				return;
			}

			// If we are here, then we are at some of themes option pages, so enqueue css/js
			wp_enqueue_script('pukka-admin');
			wp_enqueue_script('pukka-selectbox');
			wp_enqueue_script('pukka-icheck');
			
			// Used for nav tabs on Theme settings page and Icon picker
			wp_enqueue_script('jquery-ui-tabs');

			add_thickbox(); // needed for Icon Picker
			wp_enqueue_script('pukka-fa-picker');

			// dynamic input field
			wp_enqueue_script('pukka-dynamic-input');

			wp_enqueue_script('wp-color-picker');
			wp_enqueue_style('wp-color-picker');

			wp_enqueue_script('jquery-ui-autocomplete');

			wp_enqueue_style('pukka-css');
			wp_enqueue_style('font-awesome');
		}
		
		public function enqueueScripts(){
			// retina support
			wp_enqueue_script('retina_js', PUKKA_FRAMEWORK_URI . '/js/retina.js', '', '', true);
		}

		/**
		 * Here we add admin menu pages and save menu_hooknames
		 */
		public function addMenuPages() {

			// Read translation from .mo file if needed
			$this->theme_option_pages = apply_filters('pukka_translate_theme_settings', $this->theme_option_pages);

			foreach ($this->theme_option_pages as $theme_option_page) {

				/*
				if (isset($theme_option_page['custom_section']) && $theme_option_page['custom_section'] == true) {
					// We're adding new section
					$menu_hook = add_menu_page(
							$theme_option_page['page_title'], $theme_option_page['menu_title'], 'manage_options',
							$theme_option_page['page_slug'], array($this, 'printMenuPage'), '', // icon URL
							$this->menu_pos // izbaciti u argumente
					);
				} elseif (isset($theme_option_page['parent_page_slug']) && $theme_option_page['parent_page_slug'] != false) {
					// Add subpage to the existing (custom) section
					$menu_hook = add_submenu_page(
							$theme_option_page['parent_page_slug'], $theme_option_page['page_title'], $theme_option_page['menu_title'], 'manage_options',
							$theme_option_page['page_slug'], array($this, 'printMenuPage')
					);
				} else {
					// add page to the "Apereance" section
					$menu_hook = add_theme_page(
							$theme_option_page['page_title'], $theme_option_page['menu_title'], 'manage_options',
							$theme_option_page['page_slug'], array($this, 'printMenuPage')
					);
				}
				*/

				$menu_hook = add_theme_page(
							$theme_option_page['page_title'], $theme_option_page['menu_title'], 'manage_options',
							$theme_option_page['page_slug'], array($this, 'printMenuPage')
					);

				$this->menu_hooknames[$menu_hook] = $theme_option_page['page_slug'];
			}
		}

	   /**
		* Gets current menu page and sends it to the HTMLHelper to generate HTML.
		*
		* @since Pukka 1.0
		*/
		public function printMenuPage() {
			$page = $this->getMenuPage($this->menu_hooknames[current_filter()]);

			$this->html->printMenuPage($page);
		}

		/**
		 * AJAX save theme options.
		 *
		 * @since Pukka 1.0
		 */
		public function saveMenuPage() {

			$response['error'] = false;
			$response['message'] = '';
			$response['fields'] = array();


			// Verify this came from the our screen and with proper authorization
			if (!isset($_POST['pukka_nonce']) || !wp_verify_nonce($_POST['pukka_nonce'], 'pukka_framework_save')) {
				$response['error'] = true;
				$response['message'] = __('You do not have sufficient permissions to save these options.', 'pukka');
				echo json_encode($response);
				die();
			}

			// get values from db and overwrite them with new ones from $_POST
			// global
			$options_name = pukka_get_options_name();
			$pukka_values = get_option($options_name);
			$theme_style = false;
			$current_style = pukka_get_option(PUKKA_THEME_COLORSCHEME_NAME);
			foreach ($_POST['pukka'] as $key => $val) {
				// if theme style is set, remember it so that it can be processed, 
				// but only if the style is changed. We don't want to overwrite some settings
				// that user is changing
				if(PUKKA_THEME_COLORSCHEME_NAME == $key && $current_style != $val){
					$theme_style = $val;
				}
				if( is_array($val) ){
					$tmp_val = array();
					foreach( $val as $k => $v ){
						$tmp_val[] = stripslashes($v);
					}
				}
				else{
					$tmp_val = stripslashes($val);
				}

				$pukka_values[$key] = $tmp_val;
			}
						
			if(!empty($theme_style)){
				$pukka_values  = $this->pukka_set_theme_style($theme_style, $pukka_values);
			}

			update_option($options_name, $pukka_values);

			$response['message'] = __('All setting saved!', 'pukka');
			$response['fields'] = pukka_get_theme_options();
			
			echo json_encode($response);
			die();
		}

		/**
		 * AJAX reset theme options. Function checks each field to see
		 * if it has reset enabled and returns it to default value.
		 *
		 * @since Pukka 1.0
		 */
		public function resetMenuPage() {
			$response['error'] = false;
			$response['message'] = __('Reset done!', 'pukka');
			$response['fields'] = array();

			// Verify this came from our screen and with proper authorization
			if (!current_user_can('manage_options')) {
				$response['error'] = true;
				$response['message'] = __('You do not have sufficient permissions to save these options.', 'pukka');
			} else {
				$options_name = pukka_get_options_name();
				// get all theme options
				$pukka_values = get_option($options_name);
				// get options page
				$options_page = $this->getMenuPage('pukka_theme_settings_page');
				// iterate trough all tabs on page
				foreach ($options_page['tabs'] as $tab) {
					// and trough all fields on each tab
					foreach ($tab['fields'] as $field) {
						// check if field is resettable
						if (isset($field['reset']) && true == $field['reset']) {
							// if it is, set its value to empty
							if(isset($field['default'])){
								$pukka_values[$field['id']] = $field['default'];
							}else{
								$pukka_values[$field['id']] = '';
							}
						}
					}
				}
				// update options in database
				update_option($options_name, $pukka_values);
			}
			$response['fields'] = pukka_get_theme_options();
			// return JSON encoded data (response contains all the fields that were resetted)
			die(json_encode($response));
		}

		/**
		 * Printing data to document head.
		 * Meta description, Favicon, JS, CSS
		 *
		 * @since Pukka 1.0
		 */
		public function printPukkaHead() {
			
			// Print favicon
			if( pukka_get_option('favicon_id') ){
				$favicon = wp_get_attachment_image_src(pukka_get_option('favicon_id'), 'full');
				echo "\n" . '<link rel="shortcut icon" href="' . $favicon[0] . '" />' . "\n";
			}

			// Generate necessary data
			$this->cssData();
			//$this->metaData(); // called via action
			$this->jsData();

			// Print meta data
			if( !empty($this->meta) ){
				echo $this->meta;
			}

			// Print custom css
			if( !empty($this->css) ){
				echo "\n".'<style type="text/css">' ."\n";
				echo $this->css;
				echo "\n". '</style>' ."\n";
			}

			// Print custom js
			if( !empty($this->js) ){
				echo "\n".'<script type="text/javascript">' ."\n";
				echo $this->js;
				echo "\n". '</script>' ."\n";
			}

		}


		/**
		*  Generates all meta data that goes into <head> section
		*
		* @since Pukka 1.1
		*/
		public function cssData(){

			if( pukka_get_option('custom_css') != '' ){
				$this->css .= "\n" . pukka_get_option('custom_css');
			}
		}

		/**
		*  Generates all meta data that goes into <head> section
		*
		* @since Pukka 1.1
		*/
		public function jsData(){

			if( pukka_get_option('custom_js') != '' ){
				$this->js .= "\n" . pukka_get_option('custom_js');
				$lang = '';
			}			
		}

		/**
		* Generates all meta data that goes into <head> section
		*
		* @since Pukka 1.1
		*/
		public function metaData(){
			global $post, $paged;

			// If there are no SEO plugins activated
			if( !defined('WPSEO_VERSION') && !class_exists('All_in_One_SEO_Pack_Module') ){

				// Get Meta description tag
				$description = $this->getMetaDescription();

				if( $description != '' ){
					// trim description if needed
					if( strlen($description) > $this->meta_desc_length ){
						$description = mb_substr($description, 0, $this->meta_desc_length, 'UTF-8') .'...';
					}

					$this->meta .= '<meta name="description" content="'. esc_attr($description) .'" />' ."\n";
				}

				// prev/next rel links for categories, tags
				if ( get_previous_posts_link() ) {
					$this->meta .= "\n". '<link rel="prev" href="'. get_pagenum_link($paged-1) .'" />';
				}

				if ( get_next_posts_link() ) {
					$this->meta .= "\n". '<link rel="next" href="'. get_pagenum_link($paged+1) .'" />';
				}

			}
		}


		/**
		* Gets description for aprropriate object, optionally removes tags
		*
		* @param bool $strip_tags whether to strip tags or not
		*
		* @return string
		*/
		public function getMetaDescription($strip_tags=true){
			global $post;

			$description = '';

			if( !is_front_page() && (is_single() || is_page()) ){
				// excerpt could be too short, so we use content instead
				$description = apply_filters('content', $post->post_content);
			}
			elseif( is_category() ){
				$description = category_description();
			}
			elseif( is_tag() ){
				$description = tag_description();
			}
			elseif( is_home() || is_front_page() ){
				$description = get_bloginfo('description');
			}

			if( $description != '' && $strip_tags ){
				// strip all tags and left over line breaks and white space characters
				$description = wp_strip_all_tags($description, true);
			}

			return strip_shortcodes($description);
		}

		/**
		* Printing Analytics code
		*
		* @since Pukka 1.0
		*
		*/
		public function printAnalytics() {

			if( pukka_get_option('analytics_code') != '' ) {
				echo "\n" . pukka_get_option('analytics_code') . "\n";
			}
		}

		/**
		* Print info for easier debugging
		*
		* @since Pukka 1.0
		*
		*/
		public function printPukkaInfo() {

			$out = '';
			$out .= "\n". '<!--';
			if( defined('PUKKA_THEME_VERSION') ){
				$out .= "\n". 'Theme version: '. PUKKA_THEME_VERSION;
			}

			if( defined('PUKKA_VERSION') ){
				$out .= "\n". 'Pukka version: '. PUKKA_VERSION;
			}

			$out .= "\n". '-->' ."\n";

			echo $out;
		}
		
		/**
		 * Fetching selected google fonts and effects
		 *
		 * @since Pukka 1.0
		 *
		 */		
		private function fetchGoogleFonts() {
			$css = '';
			global $pukka_options_list;
			$elements = array(
							'title' => 'h1, h2, h3, h4, h5, h6', 
							'text' => 'body', 
						);
			
			$font_effects_list = "\n var fontEffects = new Array();";
			
			$cyrillic_char_set = pukka_get_option('enable_cyrillic_char_set');
			foreach($elements as $key => $element){
				$css .= "\n{$element} {";
				
				$font = pukka_get_option($key . '_font');
				$font_effect = pukka_get_option($key . '_font_effect');
				
				if(!empty($font)){
					$css .= "\n font-family: '" . str_replace('+', ' ', $font) . "';";
					
					$url = '//fonts.googleapis.com/css?family=' . $font . ':100,200,300,400,500,600,700';
					if (!empty($font_effect) && $font_effect != 'none') {
						$url .= '&effect=' . $font_effect;						
					}
									
					if('on' == $cyrillic_char_set){
						$url .= '&subset=latin,latin-ext,cyrillic';
					}else{
						$url .= '&subset=latin,latin-ext';
					}
									
					$this->meta .= "\n" . '<link rel="stylesheet" type="text/css" href="' . $url . '">';
				}
							
				if(!empty($font_effect)){
					//todo: ovaj deo se hendla javascriptom, smisliti kako da se doda
					$font_effects_list .= "\n fontEffects.push({'effect' : '$font_effect', 'target' : '$element'});";
				}

				
				$font_size = pukka_get_option($key . '_font_size');
				if(!empty($font_size)){
					$css .= "\n font-size: {$font_size}px;";
				}
				$font_weight = pukka_get_option($key . '_font_weight');
				if(!empty($font_weight)){
					$css .= "\n font-weight: {$font_weight};";
				}
								
				$font_color = pukka_get_option($key . '_font_color');
				if(!empty($font_color)){
					$css .= "\n color: {$font_color};";
				}
				
				$css .= "\n}\n";				
			}
			
			$this->js .= "\n" . $font_effects_list . "\n";

			$this->css .= $css;
			
			// custom font include code
			$custom_fonts = pukka_get_option('custom_fonts');
			if(!empty($custom_fonts)){
				$this->meta .= $custom_fonts;
			}			
		}
		
		
		private function addThemeStyle(){
			$theme_style = pukka_get_option(PUKKA_THEME_COLORSCHEME_NAME);
			global $theme_style_settings;
			if(!empty($theme_style) && 'none' != $theme_style){
				foreach($theme_style_settings[$theme_style] as $key => $val){
					if(empty($val['selector'])) continue;
					$this->css .= $val['selector'] . '{';
					foreach($val['attributes'] as $attribute){
						if(!empty($attribute['key'])){
							$this->css .= $attribute['key'] . ':' . $attribute['value'] . ';';
						}
					}
					$this->css .= '}';
				}
			}
		}
		
		public function pukka_set_theme_style($theme_style, $theme_options){
			global $theme_style_settings;
			if(!empty($theme_style) && 'none' != $theme_style){
				foreach($theme_style_settings[$theme_style] as $key => $val){
					foreach($val['attributes'] as $attribute){
						if(!empty($attribute['settings_id'])){
							if(is_array($attribute['settings_id'])){
								foreach($attribute['settings_id'] as $k => $setting){
									$theme_options[$setting] = $attribute['value'];
								}
							}else{
								$theme_options[$attribute['settings_id']] = $attribute['value'];
							}
						}
					}
				}
			}
			return $theme_options;
		}


		/* BEGIN: Retina support **********************/
		/**
		 * Retina images
		 *
		 * This function is attached to the 'wp_generate_attachment_metadata' filter hook.
		 */
		public function retinaSupportAttachmentMeta($metadata, $attachment_id){
			foreach ( $metadata as $key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $image => $attr ) {
						if ( is_array( $attr ) )
							$this->retinaSupportCreateImages( get_attached_file( $attachment_id ), $attr['width'], $attr['height'], true );
					}
				}
			}

			return $metadata;
		}

		/**
		 * Create retina-ready images
		 *
		 * Referenced via retinaSupportAttachmentMeta().
		 */
		public function retinaSupportCreateImages($file, $width, $height, $crop = false){
			if ( $width || $height ) {
				$resized_file = wp_get_image_editor( $file );
				if ( ! is_wp_error( $resized_file ) ) {
					$filename = $resized_file->generate_filename( $width . 'x' . $height . '@2x' );

					$resized_file->resize( $width * 2, $height * 2, $crop );
					$resized_file->save( $filename );

					$info = $resized_file->get_size();

					return array(
						'file' => wp_basename( $filename ),
						'width' => $info['width'],
						'height' => $info['height'],
					);
				}
			}
			return false;
		}

		/**
		 * Delete retina-ready images
		 *
		 * This function is attached to the 'delete_attachment' filter hook.
		 */
		function deleteRetinaSupportImages( $attachment_id ) {
			$meta = wp_get_attachment_metadata( $attachment_id );
			// Occurs when thumb generating fails (for examplet: timeout error during import)
			if( !is_array($meta) ){
				return;
			}

			$upload_dir = wp_upload_dir();
			$path = pathinfo( $meta['file'] );
			foreach ( $meta as $key => $value ) {
				if ( 'sizes' === $key ) {
					foreach ( $value as $sizes => $size ) {
						$original_filename = $upload_dir['basedir'] . '/' . $path['dirname'] . '/' . $size['file'];
						$retina_filename = substr_replace( $original_filename, '@2x.', strrpos( $original_filename, '.' ), strlen( '.' ) );
						if ( file_exists( $retina_filename ) )
							unlink( $retina_filename );
					}
				}
			}
		}
		/* END: Retina support **********************/

} // Class end
endif;