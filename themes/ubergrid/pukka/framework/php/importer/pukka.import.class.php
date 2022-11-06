<?php
if (!current_user_can('manage_options'))
	die();

if (!class_exists('PukkaImport')) :

	/**
	 * This class handles demo content import
	 */
	class PukkaImport {

		private $menu_hooknames = array();
		private $is_automatic = true;

		/**
		 * Class constructor, includes needed scripts and sets up AJAX requests
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function __construct() {
			// Include required files
			include_once(PUKKA_FRAMEWORK_DIR . '/php/importer/wordpress.importer.class.php');

			// Scripts
			add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'), 9);

			// AJAX hooks
			add_action('wp_ajax_pukka_start_import', array(&$this, 'pukkaStartImport'));
			add_action('wp_ajax_pukka_setup_theme_options', array(&$this, 'pukkaSetupThemeOptions'));
		}

		/**
		 * Enqueues necessary admin scripts and styles.
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function enqueueAdminScripts($hook) {
			wp_enqueue_script('pukka-import', PUKKA_FRAMEWORK_URI . '/js/jquery.pukka.import.js', array('jquery'));

			wp_enqueue_style('pukka-css');
		}


		/**
		 * Starts import process
		 * and activated
		 *
		 * @since Pukka 1.0
		 */
		public function pukkaStartImport() {
			$res = array('error' => true);
			if (!current_user_can('manage_options')) {
				die(json_encode($res));
			}
			// Creating importer object which is extended from WordPress importer
			$importer = new WP_Pukka_Import();
			// We need to tell importer to fetch attachments, so...
			$importer->fetch_attachments = true;
			// this XML file contains import data
			$file = PUKKA_DIR . '/util/demo_content/pukka_demo.xml';
			// start buffer so some details about import are intercepted
			ob_start();
			// call the function that does all the magic
			$importer->import($file);
			// end buffering and discard collected output
			ob_end_clean();

			$res['error'] = false;

			die(json_encode($res));
		}

		/**
		 * This function imports demo theme options
		 *
		 * @since Pukka 1.0
		 *
		 * @global type $pukka_theme_options Base64 encoded string containing theme options
		 */
		public function pukkaSetupThemeOptions() {
			$res = array('error' => true);

			if (!current_user_can('manage_options')) {
				die(json_encode($res));
			}

			// demo theme options are stored in $pukka_theme_options global variable
			// located in /ubergrid/pukka/util/init-theme-options.php
			global $pukka_theme_options;
			// first decode data
			
			if(is_array($pukka_theme_options)){
				foreach($pukka_theme_options as $key => $val){
					$options = json_decode(base64_decode($val), true);

					// and set value in options table
					update_option($key, $options);
					// then we get all pages
				}
			}else{
				$options = json_decode(base64_decode($pukka_theme_options), true);

				// and set value in options table
				update_option(PUKKA_OPTIONS_NAME, $options);
				// then we get all pages
			}
			
			$pages = get_pages(array('hierarchical' => false,
									'sort_order' => 'DESC',
									'sort_column' => 'ID'
								));
			$home_page = false;
			// and find imported Home page
			foreach ($pages as $page) {
				if ('home page' == strtolower($page->post_title)) {
					$home_page = $page->ID;
					break;
				}
			}
			// we tell wordpress to use Home page as, well, home page
			if ($home_page) {
				update_option('page_on_front', $home_page);
				update_option('show_on_front', 'page');
			}
			
			$menus = array( //slug => menu_location
							'main-menu' => 'primary', 
							'secondary-menu' => 'secondary', 
							'footer-menu' => 'footer'
						);
						
			$theme = wp_get_theme();
			$theme_mods = get_option('theme_mods_' . strtolower($theme->get('Name')));
			if(empty($theme_mods)){
				$theme_mods = array();
			}
			$nav_menu_locations = array();
			
			foreach($menus as $key => $val){
				$menu = get_term_by('slug', $key, 'nav_menu');
				if(!empty($menu)){
					$nav_menu_locations[$val] = $menu->term_id;
				}
			}
			
			$theme_mods['nav_menu_locations'] = $nav_menu_locations;
			
			update_option('theme_mods_' . strtolower($theme->get('Name')), $theme_mods);

			$res['options'] = pukka_get_theme_options();

			$res['error'] = false;
			$res['url'] = home_url();
			// end
			die(json_encode($res));
		}

	}

endif;