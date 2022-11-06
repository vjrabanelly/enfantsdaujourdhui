<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');
if(!class_exists('DynamicMetaDivider')) :

	class DynamicMetaDivider implements DMInterface {
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
		
		/**
		 * Class constructor
		 *  
		 * @since Pukka 1.0
		 *
		 * @param string $name Title for the dynamic meta box
		 * @param string $slug Slug of the dynamic meta box
		 */
		public function __construct($name = 'Divider', $slug = 'divider') {
			$this->name = $name;
			$this->slug = $slug;
					
			$this->min_width = 100;
			$this->max_width = 100;
			$this->step = 0;
			
			$this->default = array(
				'size'  => '100',
				'type'  => $slug,
				'title' => '',
				'content' => ''            
			);
		}
		
		/**
		 * Adding javascript files that are needed for the meta to work
		 * 
		 * @since Pukka 1.0
		 *
		 */
		public function addScripts() {
			wp_enqueue_script('dm-divider-js', DM_URI .'/assets/js/dm/jquery.dm.divider.js', array('jquery'));
		}
		
		/**
		 * Adding css files that are needed for the meta to work
		 * 
		 * @since Pukka 1.0
		 *
		 */
		public function addStyles() {
			wp_enqueue_style('dm-divider', DM_URI .'/assets/css/dm/dm-divider.css');
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
				$content->bg_color = '';
				$content->height = '';
				$content->margin_top_bottom = '';
			}
			$out = '<li class="dynamic-meta-box dm-type-divider" style="width: ' . esc_attr($data['size']) . '%;">
						<div class="dm-size-controls">
							<div class="controls-bg">
								<div class="dm-remove">R</div>
								<input type="button" class="dm-edit dm-data-input" value="' . esc_attr($content->bg_color) . '" data-var="bg_color"/>
							</div>
						</div>
						<div class="dynamic-meta-box-wrap">
							<div class="dynamic-meta-box-title"><div class="mbox-left-corner"></div>' . esc_html(__($this->name, 'pukka')) . '<div class="mbox-right-corner"></div></div>
							<div class="dm-content-wrap">
								<input type="hidden" name="_pukka_dynamic_meta_type[]" value="' . esc_attr($this->slug) . '" class="dm-type"/>
								<input type="hidden" name="_pukka_dynamic_meta_size[]" value="' . esc_attr($data['size']) . '" class="dm-size"  data-min="' . esc_attr($this->min_width) . '" data-max="' . esc_attr($this->max_width) . '" data-step="' . esc_attr($this->step) . '" />
								<input type="hidden" name="_pukka_dynamic_meta_content[]" value="' . esc_attr($data['content']) . '" class="dm-content" />
								<input type="hidden" name="_pukka_dynamic_meta_title[]" value="' . esc_attr($data['title']) . '"  />
								<div class="div-line">
									<div class="input-wrap">
										<label>' . __('Divider Height', 'pukka') . '</label>
										<input type="text" data-var="height" class="numeric-updown dm-data-input" value="' . esc_attr($content->height) . '" />
									</div>
									<div class="input-wrap">
										<label>' . __('Margin top/bottom', 'pukka') . '</label>
										<input type="text" data-var="margin_top_bottom" class="numeric-updown dm-data-input" value="' . esc_attr($content->margin_top_bottom) . '" />
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
			$content = json_decode($data['content']);
			if(!empty($content->data[0])){
				$content = $content->data[0];
			}
			
			$out = "<div class='dm-divider-box' style='width:" . esc_attr($data['size']) . "%;";
			
			if(!empty($content->height)){
				$out .= "height: {$content->height}px;";
			}
			if(!empty($content->margin_top_bottom)){
				$out .= "margin-top: {$content->margin_top_bottom}px;";
				$out .= "margin-bottom: {$content->margin_top_bottom}px;";
			}
			if(!empty($content->bg_color)){
				$out .= "background-color: {$content->bg_color};";
			}
			$out .= "'><div class='dm-divider' style='";
			if(!empty($content->line_width)){
				$out .= "height: {$content->line_width}px;";
			}
			if(!empty($content->line_color)){
				$out .= "background-color:{$content->line_color}";
			}
			$out .= "'></div>
					</div>";
			return $out;
		}
		
		/**
		 * Get metabox slug
		 *  
		 * @since Pukka 1.0
		 *
		 * @return string
		 */
		public function getName() {
			return $this->name;
		}
		
		 /**
		 * Get metabox name
		 * 
		 * @since Pukka 1.0
		 *
		 * @return string
		 */
		public function getSlug() {
			return $this->slug;
		}        
	} // end DynamicMetaDivider class
	
endif;