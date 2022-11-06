<?php 
if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');

if(!class_exists('DynamicMetaWidgetArea')) :

	class DynamicMetaWidgetArea implements DMInterface  {

		private $name;
		private $slug;
		// default meta data values
		private $default;
		// minimum width that metabox can have (in percents, from 0 to 100%)
		private $min_width;
		// maximum width that metabox can have (in percents, from 0 to 100%)
		private $max_width;
		// steps in witch size of the metabox is changed (in percents, from 0 to 100%)
		private $step;
		
		private $buffer;

		/**
		 * Class constructor
		 *
		 * @since Pukka 1.0
		 *
		 * @param string $name Title for the dynamic meta box
		 * @param string $slug Slug of the dynamic meta box
		 */
		public function __construct($name = 'Widget Area', $slug = 'widget-area') {
			$this->name = $name;
			$this->slug = $slug;

			$this->min_width = 25;
			$this->max_width = 100;
			$this->step = 25;

			$this->default = array(
				'size'  => '100',
				'type'  => $this->slug,
				'title' => '',
				'content' => ''
			);
			
			$this->buffer = '';
			
			add_action('wp_ajax_pukka_get_dynamic_sidebar', array(&$this, 'getDynamicSidebar'));
		}
		
		public function getDynamicSidebar(){
			$sidebar = trim($_POST['sidebar']);
			dynamic_sidebar($sidebar);
			
			die();
		}
			

		 /**
		 * Adding javascript files that are needed for the meta to work
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function addScripts() {
			wp_enqueue_script('dm-widget-area-js', DM_URI .'/assets/js/dm/jquery.dm.widget.area.js', array('jquery'));
		}

		/**
		 * Adding css files that are needed for the meta to work
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function addStyles() {
			wp_enqueue_style('dm-widget-area', DM_URI .'/assets/css/dm/dm-widget-area.css');
		}

		/**
		 * Generate HTML for back-end meta editor
		 *
		 * @since Pukka 1.0
		 *
		 * @param object $data
		 * @return string HTML
		 */
		public function getInputHTML($data = '') {
			if (empty($data)) {
				$data = $this->default;
			}
			if(!empty($data['content'])){
				$content = json_decode($data['content']);
				$content = $content->data[0];
			}else{
				$content = new stdClass();
				$content->sidebar_id = '';
				$content->text_color = '';
				$content->bg_color = '';
				$content->custom_classes = '';
			}
			
			if(!empty($content->sidebar_id)){
				ob_start();
				dynamic_sidebar($content->sidebar_id);				
				$widget_preview = ob_get_contents();
				ob_end_clean();
			}else{
				$widget_preview = '';
			}
			
			global $wp_registered_sidebars;
			$select_sidebar = '<select class="dm-widget-area-select dm-data-input" data-var="sidebar_id" autocomplete="off" >';
			$select_sidebar .= '<option value="">Select widget area</option>';
			
			foreach($wp_registered_sidebars as $key => $sidebar){
				$selected = '';
				if($content->sidebar_id == $key){
					$selected = 'selected';
				}
				$select_sidebar .= "<option value='$key' $selected>{$sidebar['name']}</option>";
			}
			$select_sidebar .= '</select>';
			
			$out = '<li class="dynamic-meta-box dm-type-widget-area" style="width: ' . esc_attr($data['size']) . '%;">
						<div class="dm-size-controls">
							<div class="controls-bg">
								<div class="dm-remove">R</div>
								<input type="button" class="dm-edit dm-data-input" value="' . esc_attr($content->bg_color) . '" data-var="bg_color" title="Background Color"/>
								<div class="dm-size-up">+</div>
								<div class="dm-size-down">-</div>
							</div>
						</div>
						<div class="dynamic-meta-box-wrap">
							<div class="dynamic-meta-box-title"><div class="mbox-left-corner"></div>' . esc_html(__($this->name, 'pukka')) . '<div class="mbox-right-corner"></div></div>
							<div class="dm-content-wrap">
								<input type="hidden" name="_pukka_dynamic_meta_type[]" value="' . esc_attr($this->slug) . '" class="dm-type"/>
								<input type="hidden" name="_pukka_dynamic_meta_size[]" value="' . esc_attr($data['size']) . '" class="dm-size"  data-min="' . esc_attr($this->min_width) . '" data-max="' . esc_attr($this->max_width) . '" data-step="' . esc_attr($this->step) . '" />
								<input type="hidden" name="_pukka_dynamic_meta_content[]" value="' . esc_attr($data['content']) . '" class="dm-content" />

								<input type="text" name="_pukka_dynamic_meta_title[]" value="' . esc_attr($data['title']) . '" class="dm-title dm-input" placeholder="' . __('Enter title here', 'pukka') . '"/>								
								<div class="dm-content-box ' . $content->custom_classes . '"> 
									<div class="div-line">
										<input type="text" class="dm-data-input dm-custom-classes" value="' . esc_attr($content->custom_classes) . '" data-var="custom_classes" placeholder="Custom CSS Classes" autocomplete="off" title="Custom CSS Classes" />
									</div>
									' . $select_sidebar . '
									<div class="widget-preview offer-wrap">' . $widget_preview . '</div>
									<div class="dm-input-tools">
										<div class="dm-colors-reset" title="Return colors to default values"></div>
										<input type="button" class="dm-select-color dm-data-input" value="' . esc_attr($content->text_color) . '" data-var="text_color" title="Text Color" />
									</div>
								</div>
							</div>
						</div>
					</li>';

			return $out;
		}

		/**
		 * Generates HTML for the front-end display of meta data
		 *
		 * @since Pukka 1.0
		 *
		 * @param array $data Meta data
		 * @return string HTML
		 */
		public function getOutputHTML($data) {
			if(!empty($data['content'])){
				$content = json_decode($data['content']);
				$content = $content->data[0];
			}else{
				$content = new stdClass();
				$content->sidebar_id = '';
				$content->text_color = '';
				$content->bg_color = '';
				$content->custom_classes = '';
			}
			
			$classes = 'dm-widget-area ' . $content->custom_classes;
			if('' != $content->bg_color){
				$classes .= ' colored';
			}
			
			$text_title =  $data['title'];
			
			$out = "<div class='{$classes}' style='width:" . esc_attr($data['size']) .
					"%; background-color: " . esc_attr($content->bg_color) .
					"; color: " . esc_attr($content->text_color) .
					"; float: left;'>
						<div class='widget-box-wrap'>";
			if(!empty($text_title)){
				$out .= "<div class='text-box-title'><h3 style='color: " . esc_attr($content->text_color) . ";'>" . $text_title . "</h3></div>";
			}
			
			if(!empty($content->sidebar_id)){
				ob_start();
				dynamic_sidebar($content->sidebar_id);				
				$widget_preview = ob_get_contents();
				ob_end_clean();
			}else{
				$widget_preview = '';
			}
			
			$out .= "<div class='widget-content'>" . $widget_preview . "</div>
						</div>
					</div>";
			return $out;
		}

		 /**
		 * Get metabox name
		 *
		 * @since Pukka 1.0
		 *
		 * @return string
		 */
		public function getName() {
			return $this->name;
		}

		/**
		 * Get metabox slug
		 *
		 * @since Pukka 1.0
		 *
		 * @return string
		 */
		public function getSlug() {
			return $this->slug;
		}
	} // end DynamicMetaText class

endif;