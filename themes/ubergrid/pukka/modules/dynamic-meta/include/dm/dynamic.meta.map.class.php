<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');
if(!class_exists('DynamicMetaMap')) :

	class DynamicMetaMap implements DMInterface {
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
		public function __construct($name = 'Map', $slug = 'map') {
			$this->name = $name;
			$this->slug = $slug;

			$this->min_width = 25;
			$this->max_width = 100;
			$this->step = 25;

			$this->default = array(
				'size'  => $this->max_width,
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
			wp_enqueue_script('dm-map-js', DM_URI .'/assets/js/dm/jquery.dm.map.js', array('jquery'));
			wp_enqueue_script('pukka-google-maps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', array());
		}

		/**
		 * Adding css files that are needed for the meta to work
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function addStyles() {
			wp_enqueue_style('dm-map', DM_URI .'/assets/css/dm/dm-map.css');
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
				$content->map_desc = '';
				$content->text_color = '';
				$content->bg_color = '';
				$content->map_zoom = '3';
				$content->map_lat = '';
				$content->map_lnt = '';
				$content->desc_width = '200';
				$content->map_marker = '';
				$content->map_height = '400';
			}
			$out = '<li class="dynamic-meta-box dm-type-map" style="width: ' . esc_attr($data['size']) . '%;">
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
								<div class="map-box-hide">&#x25BC;</div>
								<div class="div-line wrap-hide">
									<div class="input-wrap">
										<label>' . __('Map Height', 'pukka') . '</label>
										<input type="text" data-var="map_height" class="dm-map-desc-height numeric-updown dm-data-input" value="' . esc_attr($content->map_height) . '" />
									</div>
									<div class="input-wrap">
										<label>' . __('Zoom Level', 'pukka') . '</label>
										<input type="text" class="dm-map-zoom dm-data-input" data-var="map_zoom" readonly value="' . esc_attr($content->map_zoom) . '" />
									</div>
									<div class="input-wrap">
										<label>' . __('Latitude', 'pukka') . '</label>
										<input type="text" class="dm-map-latitude dm-data-input" data-var="map_lat" value="' . esc_attr($content->map_lat) . '" />
									</div>
									<div class="input-wrap">
										<label>' . __('Longitude', 'pukka') . '</label>
										<input type="text" class="dm-map-longitude dm-data-input" data-var="map_lnt" value="' . esc_attr($content->map_lnt) . '" />
									</div>
									<div class="input-wrap">
										<label>' . __('Desc. width', 'pukka') . '</label>
										<input type="text" data-var="desc_width" class="dm-map-desc-width numeric-updown dm-data-input" value="' . esc_attr($content->desc_width) . '" />
									</div>
								</div>
								<div class="div-line wrap-hide">
									<label>' . __('Marker icon', 'pukka') . '</label>
									<input type="text" class="dm-map-marker dm-data-input" data-var="map_marker" placeholder="' . __('Leave blank for default', 'pukka') . '" value="' . esc_attr($content->map_marker) . '" />
								</div>

								<div class="div-line wrap-hide">
									<label>' . __('Description', 'pukka') . '</label>
									<div class="dm-content-box">
											<textarea class="dm-map-description dm-input dm-data-input" data-var="map_desc" >' . esc_textarea($content->map_desc) . '</textarea>
											<!--<div class="dm-input-tools">
												<input type="button" class="dm-select-color dm-data-input" value="' . '" data-var="text_color"  title="Text Color"/>
											</div>-->
									</div>
								</div>
								 <div class="div-line">
									<div class="dm-map-container"></div>
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
			wp_enqueue_script('pukka-google-maps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', array());
			wp_enqueue_script('pukka-gmaps', get_template_directory_uri().'/js/gmaps.js', array('jquery', 'pukka-google-maps'));

			$content = json_decode($data['content']);

			if(empty($content)){
				return '';
			}

			$elem = $content->data[0];

			if(empty($elem->bg_color)){
				$elem->bg_color = '';
			}
			if(empty($elem->text_color)){
				$elem->text_color = '';
			}

			$out = "<div class='map-box' style='";
			if('100' == $data['size']){
				$out .= "width: calc(100% + 70px); margin-left: -35px; margin-right: -35px;";				
			}else{
				$out .= "width:" . esc_attr($data['size']) . "%;";
			}
			$out .= "'>";
			
			if(!empty($data['title'])){
				if('on' != pukka_get_option('dm_enable_html')){
					$text_title =  esc_html($data['title']);
				}else{
					$text_title =  $data['title'];
				}
				$out .= "        <div class='map-box-title' style='background-color: ". esc_attr($elem->bg_color) .";'><h3 style='color: " . esc_attr($elem->text_color) . ";'>" . $text_title . "</h3></div>";
			}
			$out .= "     <div class='map-box-content' data-content='" . esc_attr($data['content']) . "' style='height: " . esc_attr($elem->map_height) . "px;'></div>
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
	} // end DynamicMetaMap class

endif;