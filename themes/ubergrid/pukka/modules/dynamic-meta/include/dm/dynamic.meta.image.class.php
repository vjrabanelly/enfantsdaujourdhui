<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');
if(!class_exists('DynamicMetaImage')) :

	class DynamicMetaImage implements DMInterface  {

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
		public function __construct($name = 'Image', $slug = 'image') {
			$this->name = $name;
			$this->slug = $slug;

			$this->min_width = 25;
			$this->max_width = 100;
			$this->step = 25;

			$this->default = array(
				'size'  => '50',
				'type'  => $this->slug,
				'title' => '',
				'content' => ''
			);

			add_action('wp_ajax_pukka_get_image_url', array(&$this, 'getImageURL')); // get image url by id and size class
		}

		/**
		 * This function is attached to AJAX call and returns image URL of the
		 * requested size (user can request one of the image size classes that are defined
		 * with add_image_size() function). 
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function getImageURL(){
			$ret = array('error' => true, 'url' => '');
			if(!current_user_can('manage_options')) die(json_encode($ret));

			$img_id = intval(trim($_POST['img_id']));
			$img_size = trim($_POST['img_size']);
			if(!empty($img_id)){
				$img = wp_get_attachment_image_src($img_id, $img_size);
				if(!empty($img)){
					$ret['error'] = false;
					$ret['url'] = $img[0];
				}
			}

			die(json_encode($ret));
		}

		/**
		 * Adding javascript files that are needed for the meta to work
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function addScripts() {
			wp_enqueue_script('dm-image-js', DM_URI .'/assets/js/dm/jquery.dm.image.js', array('jquery'));
		}

		/**
		 * Adding css files that are needed for the meta to work
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function addStyles() {
			wp_enqueue_style('dm-image', DM_URI .'/assets/css/dm/dm-image.css');
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

			$default_url = PUKKA_URI . '/images/no-image.jpg';

			if(!empty($data['content'])){
				$content = json_decode($data['content']);
				$content = $content->data[0];
				$img = wp_get_attachment_image_src($content->image_id, $content->image_size);
				$content->image_url = $img[0];
			}else{
				$content = new stdClass();
				$content->text_color = '';
				$content->bg_color = '';
				$content->image_id = '';
				$content->image_url = $default_url;
				$content->image_size = 'full';
			}
			global $_wp_additional_image_sizes;
			$img_size_select = '
				<select class="image-size-select dm-data-input" data-var="image_size" title="Select size of the image to load on page">
					<option value="full">Full size</option>' . "\n";
			foreach($_wp_additional_image_sizes as $name => $img_size){
				$img_size_select .= "<option value='$name' ";
				if($name == $content->image_size){
					$img_size_select .= 'selected ';
				}
				$img_size_select .= ">{$img_size['width']}x{$img_size['height']} ";
				if(!empty($img_size['crop'])){
					$img_size_select .= '[cropped]';
				}
				$img_size_select .= "</option>\n";
			}
			$img_size_select .= '</select>';

			$out = '<li class="dynamic-meta-box dm-type-' . $this->slug . '" style="width: ' . esc_attr($data['size']) . '%;">
						<div class="dm-size-controls">
							<div class="controls-bg">
								<div class="dm-remove" title="Remove">R</div>
								<input type="button" class="dm-edit dm-data-input" value="' . esc_attr($content->bg_color) . '" data-var="bg_color" title="Background Color"/>
								<div class="dm-size-up" title="Enlarge">+</div>
								<div class="dm-size-down" title="Reduce">-</div>
							</div>
						</div>
						<div class="dynamic-meta-box-wrap">
							<div class="dynamic-meta-box-title"><div class="mbox-left-corner"></div>' . esc_html(__($this->name, 'pukka')) . '<div class="mbox-right-corner"></div></div>
							<div class="dm-content-wrap">
								<input type="hidden" name="_pukka_dynamic_meta_type[]" value="' . esc_attr($this->slug) . '" class="dm-type"/>
								<input type="hidden" name="_pukka_dynamic_meta_size[]" value="' . esc_attr($data['size']) . '" class="dm-size"  data-min="' . esc_attr($this->min_width) . '" data-max="' . esc_attr($this->max_width) . '" data-step="' . esc_attr($this->step) . '" />
								<input type="hidden" name="_pukka_dynamic_meta_content[]" value="' . esc_attr($data['content']) . '" class="dm-content" />

								<input type="text" name="_pukka_dynamic_meta_title[]" value="' . esc_attr($data['title']) . '" class="dm-title dm-input" placeholder="' . __('Enter title here', 'pukka') . '"/>
								<div class="dm-content-box image-box">
									<div class="dm-image-controls">
										' . $img_size_select . '
										<div class="dm-remove-image" title="Remove image">remove</div>
										<div class="dm-tool-loading"></div>
									</div>
									<img src="' . esc_attr($content->image_url) . '" alt="' . esc_attr($data['title']) . '" class="dm-image-preview" data-default="' . esc_attr($default_url) .'" title="Select image" />
									<input type="hidden" class="dm-image-id dm-data-input" data-var="image_id" value="' . esc_attr($content->image_id) . '"/>
									<input type="hidden" class="dm-image-url dm-data-input" data-var="image_url" value="' . esc_attr($content->image_url) . '"/>
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
			$default_url = PUKKA_URI . '/images/no-image.jpg';

			if(!empty($data['content'])){
				$content = json_decode($data['content']);
				$content = $content->data[0];
			}else{
				$content = new stdClass();
				$content->text_color = '';
				$content->bg_color = '';
				$content->image_id = '';
				$content->image_url = $default_url;
				$content->image_size = 'full';
			}
			$url = $content->image_url;

			if('full' != $content->image_size){
				$img = wp_get_attachment_image_src($content->image_id, $content->image_size);
				$url = $img[0];
			}
			$out = "<div class='image-box' style='width:" . esc_attr($data['size']) .
					"%; background-color: " . esc_attr($content->bg_color) .
					"; color: " . esc_attr($content->text_color) .
					"; float: left;'>
						<div class='image-box-wrap'>";
			if(!empty($data['title'])){
				$text_title =  $data['title'];				
				$out .= "<div class='image-box-title'><h4 style='color: " . esc_attr($content->text_color) . ";'>" . $text_title . "</h4></div>";
			}
			$out .= "<div class='image-box-content'><img src='" . esc_attr($url) . "' alt='" . esc_attr($data['title']) . "' /></div>
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
	} // end DynamicMetaImage class

endif;