<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');
if(!class_exists('DynamicMetaCTA')) :

	class DynamicMetaCTA implements DMInterface  {

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
		public function __construct($name = 'Call To Action', $slug = 'cta') {
			$this->name = $name;
			$this->slug = $slug;

			$this->min_width = 25;
			$this->max_width = 100;
			$this->step = 25;

			$this->default = array(
				'size'  => '100',
				'type'  => 'text',
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
			wp_enqueue_script('dm-cta-js', DM_URI .'/assets/js/dm/jquery.dm.cta.js', array('jquery'));
		}

		/**
		 * Adding css files that are needed for the meta to work
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function addStyles() {
			wp_enqueue_style('dm-cta', DM_URI .'/assets/css/dm/dm-cta.css');
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
				$content->text_content = '';
				$content->text_color = '';
				$content->bg_color = '';
				$content->cta_button_text = '';
				$content->cta_button_link = '';
			}
			$out = '<li class="dynamic-meta-box dm-type-cta" style="width: ' . esc_attr($data['size']) . '%;">
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
								<div class="dm-content-box">
									<!--<textarea class="dm-input dm-text-content dm-data-input" placeholder="' . __('Enter content here', 'pukka') . '" data-var="text_content">' . esc_textarea($content->text_content) . '</textarea>-->
									<input type="text" data-var="cta_button_text" class="dm-cta-button dm-data-input" value="' . esc_attr($content->cta_button_text) . '" placeholder="' . __('Call To Action Button Text', 'pukka') . '"/>
									<input type="text" data-var="cta_button_link" class="dm-cta-link dm-data-input" value="' . esc_attr($content->cta_button_link) . '" placeholder="' .__('Call To Action Link', 'pukka') . '"/>
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
				$content->text_color = '';
				$content->bg_color = '';
				$content->cta_button_text = '';
				$content->cta_button_link = '';
			}
			$classes = 'cta-box';
			if('' != $content->bg_color){
				$classes .= ' colored';
			}
			
			$text_title =  $data['title'];
			$cta_button_text = $content->cta_button_text;
			
			
			$out = "<div class='{$classes}' style='width:" . esc_attr($data['size']) .
					"%; background-color: " . esc_attr($content->bg_color) .
					"; color: " . esc_attr($content->text_color) .
					"; float: left;'>";
			
			$out .= "<div class='cta-wrap clearfix'>
						<h3 style='color: " . esc_attr($content->text_color) . ";'>" . $text_title . "</h3>
						<a href='" . esc_attr($content->cta_button_link) . "' class='cta-button button'>" . do_shortcode($cta_button_text) . "</a>
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