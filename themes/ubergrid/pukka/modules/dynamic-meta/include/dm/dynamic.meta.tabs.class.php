<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');
if(!class_exists('DynamicMetaTabs')) :

	class DynamicMetaTabs implements DMInterface {
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
		public function __construct($name = 'Tabs', $slug = 'tabs') {
			$this->name = $name;
			$this->slug = $slug;
			
			$this->min_width = 25;
			$this->max_width = 100;
			$this->step = 25;
			
			$this->default = array(
				'size'  => '100',
				'type'  => 'tabs',
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
			wp_enqueue_script('dm-tabs-js', DM_URI .'/assets/js/dm/jquery.dm.tabs.js', array('jquery'));
		}

		/**
		 * Adding css files that are needed for the meta to work
		 * 
		 * @since Pukka 1.0
		 *
		 */
		public function addStyles() {
			wp_enqueue_style('dm-tabs', DM_URI .'/assets/css/dm/dm-tabs.css');
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
				$elem->tab_title = '';
				$content->data[] = $elem;
				$content->data[] = $elem;
				$content->data[] = $elem;
				$content->data[] = $elem;
			}
			
			$out = '<li class="dynamic-meta-box dm-type-tabs" style="width:' . $data['size'] . '%;">
						<div class="dm-size-controls">
							<div class="controls-bg">
								<div class="dm-remove" title="Remove">R</div>
								<input type="button" class="dm-edit dm-data-input" value="' . esc_attr($content->data[0]->bg_color) . '" data-var="bg_color" title="Background Color"/>
								<div class="dm-size-up" title="Expand">+</div>
								<div class="dm-size-down" title="Reduce">-</div>
							</div>
						</div> 
						<div class="dynamic-meta-box-wrap">
							<div class="dynamic-meta-box-title"><div class="mbox-left-corner"></div>' . esc_html(__($this->name, 'pukka')) . '<div class="mbox-right-corner"></div></div>
							<div class="dm-content-wrap">
								<input type="hidden" name="_pukka_dynamic_meta_type[]" value="' . esc_attr($this->slug) . '" class="dm-type"/>
								<input type="hidden" name="_pukka_dynamic_meta_size[]" value="' . esc_attr($data['size']) . '" class="dm-size"  data-min="' . esc_attr($this->min_width) . '" data-max="' . esc_attr($this->max_width) . '" data-step="' . esc_attr($this->step) . '" />
								<input type="hidden" name="_pukka_dynamic_meta_content[]" value="' . esc_attr($data['content']) . '" class="dm-content" />
								<input type="hidden" name="_pukka_dynamic_meta_title[]" value="' . esc_attr($data['title']) . '"  />';
						 $tabs = '';
						 $body = '';
			$cnt = 0;
			foreach($content->data as $elem) :
								$tabs .= '<li class="dm-meta-tabs-box';
								if(0 == $cnt++){
									$tabs .= ' current';
								}
								$tabs .= '">
									<input type="text" placeholder="' . __('Enter title here', 'pukka') . '" class="dm-title dm-data-input" value="' . esc_attr($elem->tab_title) . '" data-var="tab_title">
											<div class="dm-remove-tab" title="Remove Tab">&times;</div>
											<div class="dm-add-tab" title="Add Tab">+</div>
										</li>';
								$body .= '<li>
									<div class="dm-content-box">    
										<textarea class="dm-input dm-text-content dm-data-input" placeholder="' . __('Enter content here', 'pukka') . '" data-var="text_content">' . esc_textarea($elem->text_content) . '</textarea>  
										<div class="dm-input-tools">
											<div class="dm-colors-reset" title="Return colors to default values"></div>
											<input type="hidden" class="dm-data-input dm-tabs-bg-color dm-color" value="' . esc_attr($elem->bg_color) . '" data-var="bg_color" />
											<input type="button" class="dm-select-color dm-data-input" value="' . esc_attr($elem->text_color) . '" data-var="text_color" title="Text color" />
											<div class="toggle-mce">
												<span>Editor ON/OFF</span>
												<input type="checkbox" class="dm-data-input dm-enable-mce" value="mce-enable" title="Enable/Disable Advance Editor" checked/>
											</div>
										</div>
									</div>
								</li>';
			endforeach;
			$out .= "<ul class='tabs-title'>{$tabs}</ul><ul class='tabs-body'>{$body}</ul>";
			$out .= '      </div>
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
		   if(empty($data['content'])){
			   return '';
		   }

		   $content = json_decode($data['content']);  

		   $tabs = '';
		   $body = '';
		   
		   $num = count($content->data);
		   $width = 100 / $num;		   
		   $cnt = 0;
		   
		   foreach($content->data as $elem) :
				$text_content = apply_filters('the_content', $elem->text_content);
				$tab_title = $elem->tab_title;
				
				$tabs .= '<li class="dm-meta-tabs-box';
				if(0 == $cnt){
					$tabs .= ' current';
				}				
				$tabs .= '" style="width: ' . $width . '%"><h4 style="color: ' . esc_attr($elem->text_color) . '; background-color: ' . esc_attr($elem->bg_color) . '">' . $tab_title . '</h4></li>';
				
				$body .= '<li ';
				if(0 == $cnt){
					$body .= 'class="current" ';
				}
				$body .= 'style="color: ' . esc_attr($elem->text_color) . '">                                
						' . $text_content . '
				</li>';
				$cnt++;
			endforeach;
			$out = "<div class='dm-tabs' style='width:" . esc_attr($data['size']) . "%;'>
						<ul class='tabs-title'>{$tabs}</ul>
						<ul class='tabs-body'>{$body}</ul>
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

	} // end DynamicMetaTabs class
	
endif;