<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');
if(!class_exists('DynamicMetaAccordion')) :

	class DynamicMetaAccordion implements DMInterface {
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
		public function __construct($name = 'Accordion', $slug = 'accordion') {
			$this->name = $name;
			$this->slug = $slug;
			$this->min_width = 25;
			$this->max_width = 100;
			$this->step = 25;
			
			$this->default = array(
				'size'  => '100',
				'type'  => 'accordion',
				'title' => '',
				'content' => ''            
			);
		}
		
		/**
		 * Enqueuing javascript files that are needed for the meta to work
		 * 
		 * @since Pukka 1.0
		 *
		 */
		public function addScripts() {
			wp_enqueue_script('dm-accordion-js', DM_URI . '/assets/js/dm/jquery.dm.accordion.js', array('jquery'));
		}
		
		/**
		 * Enqueuing css files that are needed for the meta to work
		 * 
		 * @since Pukka 1.0
		 *
		 */
		public function addStyles() {
			wp_enqueue_style('dm-accordion', DM_URI . '/assets/css/dm/dm-accordion.css');
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
			if(empty($data)){
				$data = $this->default;
			}
			if(!empty($data['content'])){
				$content = json_decode($data['content']);            
			}else{
				$content = new stdClass();
				$elem = new stdClass();
				$elem->text_content = '';
				$elem->text_color = '';
				$elem->bg_color = '';
				$elem->text_title = '';
				$content->data[] = $elem;
			}
			
			$out = '<li class="dynamic-meta-box dm-type-accordion" style="width:' . $data['size'] . '%;">
						<div class="dm-size-controls">
							<div class="controls-bg">
								<div class="dm-remove">R</div>
								<input type="button" class="dm-edit dm-data-input" value="' . esc_attr($content->data[0]->bg_color) . '" data-var="bg_color" title="Background Color"/>
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
								<input type="hidden" name="_pukka_dynamic_meta_title[]" value="' . esc_attr($data['title']) . '"  />
								<ul>';
			foreach($content->data as $elem) :
								$out .= '<li class="dm-meta-accordion-box">
									<div class="accordion-box-open">&#x25BC;</div>
									<input type="text" placeholder="Enter title here" class="dm-title dm-data-input" value="' . esc_attr($elem->text_title) . '" data-var="text_title">
									<div class="dm-content-box">    
										<textarea class="dm-input dm-text-content dm-data-input" placeholder="Enter content here" data-var="text_content">' . esc_textarea($elem->text_content) . '</textarea>  
										<div class="dm-input-tools">
											<div class="dm-colors-reset" title="Return colors to default values"></div>
											<input type="hidden" class="dm-data-input dm-acc-bg-color dm-color" value="' . esc_attr($elem->bg_color) . '" data-var="bg_color" />
											<input type="button" class="dm-select-color dm-data-input" value="' . esc_attr($elem->text_color) . '" data-var="text_color" title="Text color" />
											<div class="toggle-mce">
												<span>Editor ON/OFF</span>
												<input type="checkbox" class="dm-data-input dm-enable-mce" value="mce-enable" title="Enable/Disable Advance Editor" checked/>
											</div>
										</div>
									</div>
									<div class="dm-add-accordion">+</div>
								</li>';
			endforeach;
			$out .= '           </ul> 
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
			$out = '<div class="accordion" style="width: ' . $data['size'] . '%">';
			
			for($i = 0; $i < count($content->data); $i++){
				$elem = $content->data[$i];
				$text_content = apply_filters('the_content', $elem->text_content);
				$text_title = $elem->text_title;
			
				$out .= '<div class="acc-box-wrap" style="color: ' . esc_attr($elem->text_color) .'; background-color: ' . 
						esc_attr($elem->bg_color) . ';">
							<div class="acc-box-title">
								<h3 style="color: ' . esc_attr($elem->text_color) .'; background-color: '. 
								esc_attr($elem->bg_color) . ';">' . $text_title . '</h3>
								<div class="acc-title-arr fa fa-chevron-down"></div>
							</div>
							<div class="acc-box-content" >
								<div class="acc-text-wrap">' . $text_content . '</div>
							</div>
						</div>';
			}
			$out .= '</div>';
			return $out;
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
	} // end DynamicMetaAccordion class

endif;