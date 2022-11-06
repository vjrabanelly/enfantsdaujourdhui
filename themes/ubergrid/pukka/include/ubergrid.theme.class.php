<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');
/*
* Class with features specific to UberGrid theme
*
* Requires pukkatheme.class.php
*/


if (!class_exists('UberGridTheme')) :

	class UberGridTheme extends PukkaTheme {

		/**
		 * Class constructor
		 *
		 * @since Pukka 1.0
		 *
		 * @param array $theme_option_pages Theme options pages and options for each page
		 */
		public function __construct($theme_option_pages) {

			parent::__construct($theme_option_pages);
			
			// frontend scripts
			if(!is_admin()){
				$this->enqueueThemeScripts();
			}
		}

		/**
		 * Adding style to page
		 *
		 * @since Pukka 1.0
		 */
		public function enqueueThemeScripts() {
			$this->css .= $this->customThemeCss();
			$this->css .= $this->customBoxSizes();
			$this->css .= $this->rightSidebarCss();
			
			$this->js .= $this->customJS();
			
		}

		/**
		* Sets up some variables needed for js to function
		*
		* @since Pukka 1.1
		*
		*/
		function customJS(){
			$js = "\n";
			$sidebar_width = pukka_get_option('right_sidebar_width');
			if(empty($sidebar_width)){
				$sidebar_width = 225;
			}
			$js .= "\n var sidebarWidth = {$sidebar_width};";
			//$js .= "var numColumns = " . pukka_get_option('');
			$settings = pukka_fp_box_settings();
			if(!empty($settings['num_columns'])){
				$js .= "\n var hasColumns = true;";
				$js .= "\n var numColumns = {$settings['num_columns']};";
			}else{
				$js .= "\n var hasColumns = false;";
				$js .= "\n var numColumns = 0;";
			}
			$js .= "\n var brickWidth = {$settings['box_width']};";
			$js .= "\n var brickMargin = {$settings['margin']};";
						
			return $js;
		}
		
		/**
		* Sets up right sidebar style, and styles needed for it to be shown on front page
		*
		* @since Pukka 1.1
		*
		* @return String containing CSS
		*/
		function rightSidebarCss(){
			$css = '';
			$sidebar_width = pukka_get_option('right_sidebar_width');
			$box_options = pukka_fp_box_settings();
			if(empty($sidebar_width)){
				$sidebar_width = 225;
			}
			// this needs to be moved to some global javascript object
						
			$css .= "#sidebar-right {width: {$sidebar_width}px;}\n";
			$css .= "#sidebar-right .widget {width: " . ($sidebar_width - 40) . "px;}\n";
			// responsiv single page sidebar fix
			if(!is_home() && ! is_front_page()){
				$css .= "@media all and (max-width: " . (930 + $sidebar_width) . "px) { #sidebar-right{width:90%;}\n
				#sidebar-right .widget {margin-left: 5px; margin-right: 5px;}}";
			}
			// if front page sidebar is on, here we setup style for it
			if('on' == pukka_get_option('show_home_right_sidebar') && 'on' == pukka_get_option('use_fp_grid')){
				$css .= ".home #brick-wrap{padding-right: " . ($sidebar_width + 5) . "px}\n";
				$css .= ".home #sidebar-right{position: absolute; right: 0px; top: " . $box_options['margin'] . "px; width: " . $sidebar_width . "px;}\n";
				$css .= "@media all and (max-width: " . (680 + $sidebar_width) . "px) { body.home #sidebar-right{display:none;}\n #brick-wrap {padding-right: 0px;}\n}";
			}
			
			// grid categroy sidebar
			$css .= "\n #brick-wrap.grid-cat-sidebar {padding-right: " . ($sidebar_width + $box_options['margin']) . "px;}"; 
			$css .= "\n .grid-cat-sidebar #sidebar-right { position: absolute; top: 5px; right: 0px; width: {$sidebar_width}px; }"; 
			$css .= "\n .grid-cat-sidebar .brick-cat-title { width: 100%; width: calc(100% - " . ($sidebar_width + 2*$box_options['margin'] + 5) . "px); }"; 
			$css .= "\n .grid-cat-sidebar.no-sidebar .brick-cat-title { width: 100%; width: calc(100% - " . (2 * $box_options['margin']) . "px); }"; 
			
			return $css;
		}
		
		/**
		* Sets up sizes of the front page boxes (width, height, margins, number of columns)
		*
		* @since Pukka 1.0
		*
		* @return String containing CSS
		*/
		public function customBoxSizes(){
			$css = '';
			$box_options = pukka_fp_box_settings();
			
			if(!empty($box_options['box_width'])){
				$css .= ".brick-big {width:" . (($box_options['box_width'] + $box_options['margin']) * 2) . "px;}\n";
				$css .= ".brick-medium {width:" . $box_options['box_width'] . "px;}\n";
				$css .= ".brick-small {width:" . $box_options['box_width'] . "px;}\n";
			}else{
				$box_options['box_width'] = 225;
			}
						
			if(!empty($box_options['box_height'])){
				$css .= ".brick-big {height:" . $box_options['box_height'] . "px;}\n";
				$css .= ".brick-medium {height:" . $box_options['box_height']. "px;}\n";
				$css .= ".brick-small {height:" . ($box_options['box_height'] / 2 - $box_options['margin']) . "px;}\n";
			}
			
			if(!empty($box_options['big_img_height'])){
				$css .= '.brick-media {max-height: ' . $box_options['big_img_height'] . 'px;}';
			}
			
			if(!empty($box_options['small_img_height'])){
				$css .= '.brick-small .brick-media {max-height: ' . $box_options['small_img_height'] . 'px;}';
			}
			
						
			if(!empty($box_options['num_columns'])){
				$css .= "#brick-wrap {max-width: " . (($box_options['box_width'] + 2*$box_options['margin']) * $box_options['num_columns']) . "px;}\n";				
			}
			$css .= ".brick-cat-title {width: calc(100% - " . (2*$box_options['margin']) . "px);}";
			
			$css .= ".brick{margin: " . $box_options['margin'] . "px;}\n
					#brick-wrap{ margin-left: -" . $box_options['margin'] . "px}\n";
			$css .= "@media all and (max-width: 700px) { #brick-wrap{margin-left:auto;}}\n";
			
			return $css;
		}

		/**
		 * Generating CSS from user defined options
		 *
		 * @since Pukka 1.0
		 *
		 * @return string Generated CSS
		 */
		public function customThemeCss() {
			$css = '';

			if (pukka_get_option('text_color') != '' || pukka_get_option('body_bg_color') != '') {
				$css .= "\n" . 'body { ';

				if (pukka_get_option('text_color') != '') {
					$css .= 'color: ' . pukka_get_option('text_color') . ' !important;';
				}

				if (pukka_get_option('body_bg_color') != '') {
					$css .= 'background-color: ' . pukka_get_option('body_bg_color') . ' !important;';
				}

				$css .= '}';
			}

			$logo_color = pukka_get_option('text_logo_color');
			if(!empty($logo_color)){
				$css .= "\n#logo-text{color: {$logo_color};}" ;
			}

			$sidebar_menu_color = pukka_get_option('sidebar_text_color');
			if(!empty($sidebar_menu_color)){
				$css .= "\n#main-menu li a, #main-menu li a:visited, #social-menu a, #social-menu a:visited, #sidebar, #sidebar a, #sidebar a:visited, #copy {color: {$sidebar_menu_color};}";
			}


			$sidebar_menu_hover = pukka_get_option('sidebar_text_hover');
			if(!empty($sidebar_menu_hover)){
				$css .= "\n#main-menu li a:hover {color: {$sidebar_menu_hover};}";
			}

			$sidebar_submenu_color = pukka_get_option('sidebar_submenu_text_color');
			if(!empty($sidebar_submenu_color)){
				$css .= "\n#main-menu .sub-menu li a, #main-menu .sub-menu li a:visited {color: {$sidebar_submenu_color};}";
			}


			$sidebar_submenu_hover = pukka_get_option('sidebar_submenu_text_hover');
			if(!empty($sidebar_submenu_hover)){
				$css .= "\n#main-menu .sub-menu li a:hover {color: {$sidebar_submenu_hover};}";
			}


			if (pukka_get_option('heading_color') != '') {
				$css .= "\n" . 'h1, h2, h3, h4, h5, h6, h1 a, ';
				$css .= 'h2 a, h3 a, h4 a, h5 a, h6 a, ';
				$css .= 'h1 a:visited, h2 a:visited, h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited {';
				$css .= 'color: ' . pukka_get_option('heading_color') . ' !important;';
				$css .= '}';
			}

			if (pukka_get_option('link_color') != '') {
				$css .= "\n" . 'a, a:visited{ color: ' . pukka_get_option('link_color') . '; }';
			}

			if (pukka_get_option('link_color') != '') {
				$css .= "\n" . 'a, a:visited{ color: ' . pukka_get_option('link_color') . '; }';
			}

			// big brick
			if (pukka_get_option('stripe_1_color') != '') {
				$css .= "\n" . '.brick-big .stripe{ background-color: ' . pukka_get_option('stripe_1_color') . '; }';
				$css .= "\n" . '.brick-big .brick-format{ color: ' . pukka_get_option('stripe_1_color') . '; }';
			}

			// medium brick
			if (pukka_get_option('stripe_2_color') != '') {
				$css .= "\n" . '.brick-medium .stripe{ background-color: ' . pukka_get_option('stripe_2_color') . '; }';
				$css .= "\n" . '.brick-medium .brick-format{ color: ' . pukka_get_option('stripe_2_color') . '; }';
			}

			// small brick
			if (pukka_get_option('stripe_3_color') != '') {
				$css .= "\n" . '.brick-small .stripe{ background-color: ' . pukka_get_option('stripe_3_color') . '; }';
				$css .= "\n" . '.brick-small .brick-format{ color: ' . pukka_get_option('stripe_3_color') . '; }';
			}

			// single post, below featured image
			if (pukka_get_option('stripe_4_color') != '') {
				$css .= "\n" . '.featured .stripe{ background-color: ' . pukka_get_option('stripe_4_color') . '; }';
				$css .= "\n" . '.featured .brick-format{ color: ' . pukka_get_option('stripe_4_color') . '; }';
			}

			// button bg color
			if (pukka_get_option('button_bg_color') != '') {
				$css .= "\n" . 'button, input[type="button"], input[type="reset"], input[type="submit"]{ background-color: ' . pukka_get_option('button_bg_color') . '; }';
			}

			$title_font = pukka_get_option('title_font');
			if (!empty($title_font) && $title_font != 'default') {
				$css .= "\n" . 'h1, h2, h3, h4, h5, h6, h1 a, ';
				$css .= 'h2 a, h3 a, h4 a, h5 a, h6 a, ';
				$css .= 'h1 a:visited, h2 a:visited, h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited,';
				$css .= '#main-menu, #secondary-menu {';
				$css .= 'font-family: "' . str_replace('+', ' ', $title_font) . '" !important;';
				$css .= "}\n";
			}

			$text_font = pukka_get_option('text_font');
			if (!empty($text_font) && $text_font != 'default') {
				$css .= 'body {' . "\n";
				$css .= 'font-family: "' . str_replace('+', ' ', $text_font) . '" !important;' . "\n";
				$css .= '}' . "\n";
			}

			return $css;
			
		}

	} // Class end

endif; // end if class_exists