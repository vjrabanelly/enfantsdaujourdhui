<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');
/*
* Class which handles metabox printing and custom fields saving
*
* Requires html.helper.class.php
*/

if (!class_exists('PukkaMetaBox')) :

	class PukkaMetaBox {

		private $html; // html helper object
		private $nonce_set; // boolean, check if nonce has already been printed
		private $already_saved = false; // save is fired for each meta box
		private $meta_boxes; // array containing meta box data

		/**
		 * Class constructor
		 *
		 * @since Pukka 1.0
		 *
		 * @param array $meta_boxes Array containing meta boxes and their data
		 */

		public function __construct($meta_boxes) {

			if (basename($_SERVER['PHP_SELF']) == "post-new.php"
					|| basename($_SERVER['PHP_SELF']) == "post.php"
			) {
				$this->html = new HtmlHelper();
				$this->html->setContext('metabox');

				$this->meta_boxes = $meta_boxes;
				// add meta boxes
				add_action('add_meta_boxes', array(&$this, 'addMetaBox'));

				// save meta boxes
				add_action('save_post', array(&$this, 'saveMetaBox'));

				add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
			}
		}

		/**
		 * Enqueues necessary javascript and style
		 * some (all) scripts registered already in pukkatheme.class.php
		 *
		 * @since Pukka 1.0
		 *
		 * @param string $hook Not used atm.
		 */
		public function enqueueAdminScripts($hook) {

			wp_enqueue_script('pukka-admin');

			//TODO: check for wp 3.4 i 3.5 (http://d.pr/f/RoPE)
			wp_enqueue_script('wp-color-picker');
			wp_enqueue_style('wp-color-picker');

			wp_enqueue_style('pukka-css');
			wp_enqueue_style('pukka-icomoon');

			wp_enqueue_style('font-awesome');
			add_thickbox();
			wp_enqueue_script('pukka-fa-picker');
		}

		/**
		 * Adding meta boxes to the post types
		 *
		 * @since Pukka 1.0
		 */
		public function addMetaBox() {

			foreach ($this->meta_boxes as $meta_box) {
				add_meta_box(
						$meta_box['id'], $meta_box['title'], array(&$this, 'printMetaBox'), $meta_box['post_type'], $meta_box['context'], $meta_box['priority'], array('current_meta_box' => $meta_box)
				);
			}
		}

		/**
		 * Rendering fields of the meta box.
		 *
		 * @since Pukka 1.0
		 *
		 * @param object $post Current post
		 * @param array $metabox Array containing all the metaboxes and additional data.
		 */
		public function printMetaBox($post, $metabox) {
			$meta_box = $metabox['args']['current_meta_box'];

			//TODO: empty check
			foreach ($meta_box['fields'] as $key => $field) {
				$field['value'] = get_post_meta($post->ID, $field['id'], true) != '' ? get_post_meta($post->ID, $field['id'], true) : '';
				$this->html->printInput($field);
			}

			// print hidden data
			if (!$this->nonce_set) {
				$this->html->printNonce();
				$this->nonce_set = true;
			}
		}

		/**
		 * Saving post meta
		 *
		 * @since Pukka 1.0
		 *
		 * @global object $post Current post
		 * @param string $post_id Current post ID
		 */
		public function saveMetaBox($post_id) {
			global $post;

			// verify nonce
			if (isset($_POST['pukka_nonce'])) {

				if (isset($_POST['post_type'])) {
					$post_type_object = get_post_type_object($_POST['post_type']);
				} else {
					return;
				}

				if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)                       // check autosave
						|| (!isset($_POST['post_ID']) || $post_id != $_POST['post_ID'])         // check revision
						|| (!wp_verify_nonce($_POST['pukka_nonce'], 'pukka_nonce_save_metabox'))      // verify nonce
						|| (!current_user_can($post_type_object->cap->edit_post, $post_id)) // check permission
						|| $this->already_saved) {  // check if already saved
					return $post_id;
				}

				
				$post_meta = array();

				$allowed_tags = wp_kses_allowed_html( 'post' );
				// add iframe support
				$allowed_tags['iframe'] = array(
											'src' => true,
											'width' => true,
											'height' => true,
											'frameborder' => true,
											'webkitallowfullscreen' => true,
											 'mozallowfullscreen' => true,
											 'allowfullscree' => true,
											);

				$allowed_tags = apply_filters('pukka_allowed_tags', $allowed_tags);

				// loop through all meta boxes
				foreach ($this->meta_boxes as $meta_box) {
					// get only metaboxes for post type which is currently being saved
					if ($meta_box['post_type'] == get_post_type($post)) {

						foreach ($meta_box['fields'] as $field) {

							$old = get_post_meta($post_id, $field['id'], true);
							$new = isset($_POST['pukka'][$field['id']]) ? $_POST['pukka'][$field['id']] : '';

							// 'off' value si passed if checkbox is not checked
							if( $new == 'off' ){
								$new = '';
							}

							if( !empty($field['multiple']) && $field['multiple'] == true ){
								// 'multiple values', arrays wont be serialized

						        // get new values that need to add and get old values that need to delete
						        $add = array_diff($new, $old);
						        $delete = array_diff($old, $new);
						        
						        // add
						        foreach( $add as $add_new ){
						            add_post_meta($post_id, $field['id'], wp_kses($add_new, $allowed_tags), false);
						        }
						        
						        // delete
						        foreach( $delete as $delete_old ){
						            delete_post_meta($post_id, $field['id'], $delete_old);
						        }

							}
							else{
								// 'single' values, arrays are serialized
								if ($new && $new != $old) {
									update_post_meta($post_id, $field['id'], wp_kses($new, $allowed_tags));
								} elseif ('' == $new && $old) {
									delete_post_meta($post_id, $field['id'], $old);
								}
							}

							$post_meta[$field['id']] = $new;
						}
					}
				}

				do_action('pukka_meta_box_save', $post_id, $post_meta);
				$this->already_saved = true;
			} //if( isset($_POST['pukka_meta_box_nonce']) )
		}

	} // Class end
endif;