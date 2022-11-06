<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');
if(!class_exists('DynamicMetaContact')) :

	class DynamicMetaContact implements DMInterface {
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
		public function __construct($name = 'Contact', $slug = 'contact-form') {
			$this->name = $name;
			$this->slug = $slug;
			
			$this->min_width = 50;
			$this->max_width = 100;
			$this->step = 50;
			
			$this->default = array(
				'size'  => $this->min_width,
				'type'  => $slug,
				'title' => '',
				'content' => '',
			);
			
			add_action('wp_ajax_send_contact_form', array(&$this, 'sendContactForm'));
			add_action('wp_ajax_nopriv_send_contact_form', array(&$this, 'sendContactForm'));
		}
		
		/**
		 * When the dynamic meta contact form is submitted, an AJAX call is made to this
		 * functions that does the actual sending. Mail is sent to email address 
		 * that is set in Theme Options. If no address is set, email is sent to the
		 * admin email.
		 * 
		 * @since Pukka 1.0
		 *
		 */
		public function sendContactForm(){
			$from = trim($_POST['dm_msg_user_email']);
			$headers = 'Content-type: text/html; charset=utf-8' . "\r\n";
			$headers .= 'From: ' .$from . "\r\n";
			$subject = get_bloginfo('name') . ' contact message';
			$msg = esc_html(trim($_POST['dm_msg_text']));
			$return = array('error' => false, 'message' => '');
			
			// spam check
			$numbers = trim(pukka_get_option('form_spam_numbers'));
			if(!empty($numbers)){
				$numbers = explode(',', pukka_get_option('form_spam_numbers'));
				if(is_array($numbers) && count($numbers) > 0){
					for($i = 1; $i <= 10; $i++){
						if(!empty($_POST[$i]) && !in_array($_POST[$i], $numbers) || (empty($_POST[$i]) && in_array($i, $numbers))){
							$return['error'] = true;
							$return['message'] = pukka_get_option('form_error');
							break;
						}
					}
				}
			}
			
			if(empty($msg)){
				$return['error'] = true;
				$return['message'] = pukka_get_option('form_error');
			}
			
			if(!$return['error']){
				$to = pukka_get_option('form_email');
				if(empty($to)){
					$to = get_option('admin_email');
				}
				
				$res = mail($to, $subject, $msg, $headers);
							
				if($res){
					$return['error'] = false;
					$return['message'] = pukka_get_option('form_thank_you');
				}else{
					$return['error'] = true;
					$return['message'] = pukka_get_option('form_error');
				}
			}
			
			die(json_encode($return));
		}
		
	   /**
		 * Adding javascript files that are needed for the meta to work
		 * 
		 * @since Pukka 1.0
		 *
		 */
		public function addScripts() {
			wp_enqueue_script('dm-contact-js', DM_URI . '/assets/js/dm/jquery.dm.contact.js', array('jquery'));
		}
		
		/**
		 * Adding css files that are needed for the meta to work
		 * 
		 * @since Pukka 1.0
		 *
		 */
		public function addStyles() {
			wp_enqueue_style('dm-contact', DM_URI . '/assets/css/dm/dm-contact.css');        
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
			}
			$out = '<li class="dynamic-meta-box dm-type-contact" style="width: ' . esc_attr($data['size']) . '%;">
						<div class="dm-size-controls">
							<div class="controls-bg">
								<div class="dm-remove">R</div>
								<input type="button" class="dm-edit dm-data-input" value="' . esc_attr($content->bg_color) . '" data-var="bg_color"  title="Background Color"/>
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
								<br/>
								<label>' . __('Email address:', 'pukka') . '</label>
								<input type="text" value="" class="dm-title dm-input" disabled />      
								<br/>    
								<label>' . __('Message:', 'pukka') . '</label>
								<div class="dm-content-box">                                    
									<div class="dm-input-tools">
										<div class="dm-colors-reset" title="Return colors to default values"></div>
										<input type="button" class="dm-select-color dm-data-input" value="' . esc_attr($content->text_color) . '" data-var="text_color"  title="Text Color"/>
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
				$content->bg_color = '';
				$content->text_color = '';
			}
			if('on' != pukka_get_option('dm_enable_html')){
				$text_title =  esc_html($data['title']);
			}else{
				$text_title =  $data['title'];
			}
			$spam_check = pukka_get_spam_fields();
			$out = "<div class='dm-contact-form-box' style='width:" . esc_attr($data['size']) ."%;background-color: " . $content->bg_color . "; color: " . $content->text_color . ";'>
						<div class='dm-contact-form-title'>" . $text_title . "</div>
						<form method='post' action='" . admin_url('admin-ajax.php') . "?action=send_contact_form' class='dm-contact-form'>
							<input type='text' name='dm_msg_user_email' value='' placeholder='". __('Your Email', 'pukka') ."' />
							<!--<input type='text' name='dm_msg_subject' value='' placeholder='". __('Subject', 'pukka') ."' />-->
							<textarea name='dm_msg_text' placeholder='". __('Message', 'pukka') ."'></textarea>
							<input type='submit' value='". __('Send', 'pukka') ."' class='dm-form-submit'/>
							<input type='hidden' name='dm_mgs_email_to' value='" . esc_attr($data['content']) . "' />
							" . $spam_check . "
						</form>
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
		
	} // end DynamicMetaContact class
	
endif;