<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');
if(!class_exists('Dynamic_Meta')) :

	class Dynamic_Meta {

		private $post_types;
		private $meta_boxes;

		/**
		 * Class constructor
		 *
		 * @since Pukka 1.0
		 *
		 * @global string $pagenow current page
		 * @param array $post_type post types that will use dynamic meta
		 * @param bool $print_nonce
		 */
		function __construct($post_type = array('post'), $print_nonce=true) {

			$this->post_types = $post_type;
			/*
			 * Including meta boxes
			 */
			require_once('dm/dm.interface.php');
			require_once('dm/dynamic.meta.text.class.php');
			require_once('dm/dynamic.meta.image.class.php');
			require_once('dm/dynamic.meta.accordion.class.php');
			require_once('dm/dynamic.meta.tabs.class.php');
			require_once('dm/dynamic.meta.contact.class.php');
			require_once('dm/dynamic.meta.map.class.php');
			require_once('dm/dynamic.meta.divider.class.php');
			require_once('dm/dynamic.meta.cta.class.php');
			require_once('dm/dynamic.meta.widget.area.class.php');

			$dm1 = new DynamicMetaText();
			$dm2 = new DynamicMetaAccordion();
			$dm3 = new DynamicMetaImage();
			$dm4 = new DynamicMetaTabs();
			$dm5 = new DynamicMetaContact();
			$dm6 = new DynamicMetaMap();
			$dm7 = new DynamicMetaDivider();
			$dm8 = new DynamicMetaCTA();
			$dm9 = new DynamicMetaWidgetArea();

			$this->meta_boxes = array(
				$dm1->getSlug() => $dm1,
				$dm2->getSlug() => $dm2,
				$dm3->getSlug() => $dm3,
				$dm4->getSlug() => $dm4,
				$dm5->getSlug() => $dm5,
				$dm6->getSlug() => $dm6,
				$dm7->getSlug() => $dm7,
				$dm8->getSlug() => $dm8,
				$dm9->getSlug() => $dm9,
			);

			// Frontend scripts
			add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'), 0);

			// Backend scripts
			global $pagenow;
			if('post.php' == $pagenow || 'post-new.php' == $pagenow){
				add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'), 0);
			}

			add_action('add_meta_boxes', array(&$this, 'addMetaBoxes')); // add meta box
			add_action('save_post', array(&$this, 'saveDMMeta')); // save meta box's data
			add_action('wp_ajax_pukka_get_dm_box', array(&$this, 'pukkaGetDMBox')); // get meta box with AJAX
		}

		/**
		 * Adding javascript and css files that are needed for the meta to work
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function enqueueAdminScripts() {
			//Javascripts
			wp_enqueue_script('numeric-updown-js', DM_URI .'/assets/js/jquery.numeric.updown.js', array('jquery'));
			wp_enqueue_script('custom-select-js', DM_URI .'/assets/js/jquery.customSelect.min.js', array('jquery'));
			wp_enqueue_script('jquery-dm', DM_URI .'/assets/js/jquery.dm.js', array('jquery', 'wp-color-picker', 'numeric-updown-js', 'custom-select-js'));

			//Styles
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style('dm-style-back', DM_URI .'/assets/css/dm.css');
			wp_enqueue_style('dm-style-front', DM_URI .'/assets/css/dm.front.css');
			wp_enqueue_style('numeric-updown', DM_URI .'/assets/css/numeric-updown.css');

			foreach($this->meta_boxes as $box){
				$box->addScripts();
				$box->addStyles();
			}
		}

		/**
		 * Adding javascript and css files that are needed for the meta to work
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function enqueueScripts() {

			//Styles
			if( file_exists( get_stylesheet_directory() .'/'. PUKKA_OVERRIDES_DIR_NAME .'/dynamic-meta/assets/css/dm.front.css') ){
				// use get_stylesheet_directory_uri for child theme support
				$dm_css_url = get_stylesheet_directory_uri() .'/'. PUKKA_OVERRIDES_DIR_NAME .'/dynamic-meta/assets/css/dm.front.css';
			}
			else{
				$dm_css_url = DM_URI .'/assets/css/dm.front.css';
			}

			wp_enqueue_style('dm-style-front', $dm_css_url);
			wp_enqueue_script('jquery-dm-front', DM_URI .'/assets/js/jquery.dm.front.js', array('jquery'));
			wp_enqueue_script('gmaps', DM_URI .'/assets/js/gmaps.js', array('jquery'));
		}

		/**
		 * Adding meta boxes
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function addMetaBoxes() {
			foreach ($this->post_types as $post_type) {
				add_meta_box(
					'pukka_dynamic_metabox_', 'Dynamic Meta Box', array(&$this, 'pukkadynamicMetabox'), $post_type, 'normal', 'high');
			}
		}

		/**
		 * This function prints dynamic meta html to post editor
		 *
		 * @since Pukka 1.0
		 *
		 * @global object $post current post
		 */
		public function pukkadynamicMetabox() {
			global $post;
			$options = get_post_meta($post->ID, '_pukka_dynamic_meta_box', true);
			$out = '';

			if (!empty($options) && count($options) > 0) {
				foreach ($options as $box) {
					$out .= $this->meta_boxes[$box['type']]->getInputHTML($box);
				}
			}

			$html = '<div id="dynamic-meta-wrapper">';

			$toolbar = '';
			foreach($this->meta_boxes as $box){
				$toolbar .= '<li data-type="' . $box->getSlug() . '" class="dm-tool-' . $box->getSlug() . '"><div class="dm-tool-loading"></div>' . $box->getName() .'</li>';
			}
			$html .= '<ul class="dm-toolbar top">' .$toolbar . '</ul><input type="hidden" name="_pukka_dm_noncename" value="' . wp_create_nonce(basename(__FILE__)) . '" />
				<ul id="dynamic-meta-content">' . $out . '</ul><ul class="dm-toolbar bottom">' . $toolbar . '</ul></div>';
			echo $html;
		}

		/**
		 * When user attempts to add dynamic meta box, an AJAX call is made to this function
		 * that returns meta box html.
		 *
		 * @since Pukka 1.0
		 *
		 */
		function pukkaGetDMBox() {
			if (!is_admin())
				die();

			$type = trim($_POST['type']);

			$out = $this->meta_boxes[$type]->getInputHTML();

			echo $out;

			die();
		}

		/**
		 * Function responsible for saving meta data of the current post
		 *
		 * @since Pukka 1.0
		 *
		 * @param int $post_id ID of the current post
		 * @return integer
		 */
		function saveDMMeta($post_id) {
			if (!is_admin())
				return;

			if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
					|| (!isset($_POST['post_ID']) || $post_id != $_POST['post_ID'])
			){
				return $post_id;
			}
			if (!isset($_POST['_pukka_dm_noncename']) || !wp_verify_nonce($_POST['_pukka_dm_noncename'], basename(__FILE__))) {
				return $post_id;
			}

			$dm = pukka_set_dm_array();

			update_post_meta($post_id, '_pukka_dynamic_meta_box', $dm);

			return $post_id;
		}

		/**
		 * Wrapper function for printing out HTML of individual meta boxes from $data array
		 *
		 * @since Pukka 1.0
		 *
		 * @param array $data Array containing all meta boxes attached to single post
		 * @return string html
		 */
		public function getDMHTML($data){
			$out = '';
			if(!empty($data)){
				foreach($data as $elem){
					$out .= $this->meta_boxes[$elem['type']]->getOutputHTML($elem);
				}
			}

			return $out;
		}
	} // end Dynamic_Meta class

endif;