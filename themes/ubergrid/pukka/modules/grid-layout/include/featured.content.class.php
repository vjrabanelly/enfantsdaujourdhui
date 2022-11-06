<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');
/*
* Class which is handles Featured content backend
* Enqueues scripts, does necessary stuff after post is saved, adds backend menu page
*
* Requires html.helper.class.php
*/

if (!class_exists('PukkaFeaturedContent')) :

	class PukkaFeaturedContent {

		private $menu_hooknames = array();
		// sizes for the content dses on front page
		protected $box_sizes;
		// array containing all the terms (only categories for now)
		private $pukka_terms;

		/**
		 * Class constructor
		 *
		 * @since Pukka 1.0
		 */
		public function __construct() {

			$this->box_sizes = array('big', 'medium', 'small');

			// Scripts
			add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'), 2);
			add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'), 9);

			// Hooks
			add_action('admin_menu', array($this, 'addMenuPages'), 1);
			add_action('transition_post_status', array($this, 'featuredPostStatusChange'), 10, 3);

			//Ajax (backend) hooks
			add_action('wp_ajax_pukka_save_featured', array(&$this, 'ajaxSaveFeaturedContent'));
			add_action('wp_ajax_pukka_add_featured_post', array(&$this, 'ajaxPostAutoComplete'));
			add_action('wp_ajax_pukka_get_featured_box', array(&$this, 'ajaxGetFeaturedBox'));

			// Save post hook
			add_action('pukka_meta_box_save', array(&$this, 'savePost'), 10, 2);
		}

		/**
		 * Front end scripts (mozda izbaciti odavde, uz ostali front)
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function enqueueScripts() {

			wp_enqueue_script('jquery-masonry');

			wp_enqueue_script('featured-content-script', GRID_URI . '/assets/js/jquery.featured.content.front.js', array('jquery', 'jquery-masonry'));
		}

		/**
		 * Enqueues necessary admin scripts and styles.
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function enqueueAdminScripts($hook) {

			// enqueue only on pages we registered
			if (!array_key_exists($hook, $this->menu_hooknames)) {
				return;
			}

			wp_enqueue_script('jquery-ui-widget');
			wp_enqueue_script('jquery-ui-mouse');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('jquery-ui-autocomplete');

			wp_enqueue_script('featured-content-script', GRID_URI . '/assets/js/jquery.featured.content.js', array('jquery-ui-sortable', 'pukka-admin'));

			wp_register_style('featured-content-style', GRID_URI . '/assets/css/featured.content.css');

			wp_enqueue_style('featured-content-style');
			wp_enqueue_style('pukka-css');

			// if( wp_style_is('pukka-icomoon', 'registered') ) { // registered in theme's parent class (pukkatheme.class.php)
				// wp_enqueue_style('pukka-icomoon');
			// }
		}

		/**
		 * Adds featured content admin page to the Appereance section of admin menu.
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function addMenuPages() {

			$menu_hook = add_theme_page(
					__('Front page manager', 'pukka'), __('Front page manager', 'pukka'), 'manage_options', // izbaciti u argumente
					'pukka_front_page_manager', array($this, 'printMenuPage')
			);

			$this->menu_hooknames[$menu_hook] = 'pukka_featured_content_page';
		}

		/**
		 * When featured post changes status from publish to trash it needs to be
		 * removed from featured posts.
		 *
		 * @since Pukka 1.0
		 *
		 * @param string $new_status
		 * @param string $old_status
		 * @param object $post
		 */
		public function featuredPostStatusChange($new_status, $old_status, $post){
			// if it's not featured, do nothing
			if(!$this->isPostFeatured($post->ID)) return;

			// if it is featured and it's moved to trash, remove it from fetured
			if('trash' == $new_status){
				$this->removePost($post->ID);
				// remove checkbox "Featured post" because the post was removed
				// from featured list, and if restored it will stay checked even
				// though it's no longer in featured posts array, so...
				delete_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'featured');
			}
		}

		/**
		 * Checks if post is in featured content
		 *
		 * @since Pukka 1.0
		 *
		 * @param int $post_id Id of the post
		 * @return boolean Returns true if post is featured, false otherwise
		 */
		public function isPostFeatured($post_id) {
			$featured_content = pukka_get_option('featured_content');

			if (empty($featured_content) || !is_array($featured_content)) {
				return false;
			}

			foreach ($featured_content as $content) {
				if ($content['type'] == 'post' && $content['id'] == $post_id) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Deletes all featured content
		 *
		 * @since Pukka 1.0
		 *
		 */
		public function deleteFeaturedContent() {

			$featured_content = pukka_get_option('featured_content');

			if (!empty($featured_content) && is_array($featured_content)) {
				foreach ($featured_content as $content) {
					if ($content['type'] == 'post') {
						delete_post_meta($content['id'], PUKKA_POSTMETA_PREFIX .'featured');
						delete_post_meta($content['id'], PUKKA_POSTMETA_PREFIX .'box_size');
					}
				}

				pukka_set_option('featured_content', '');
			}
		}

		/**
		 * Prepends  post to a featured content array
		 *
		 * @since Pukka 1.0
		 *
		 * @param int $post_id Id of the post
		 */
		public function prependPost($post_id) {

			// Check if post is already in the featured list, if so do nothing
			if ($this->isPostFeatured($post_id)) {
				return;
			}

			// Get featured content
			$featured_content = pukka_get_option('featured_content');

			$new_featured_content = array();
			$new_featured_post = array('id' => $post_id, 'type' => 'post', 'pinned' => '');

			if (empty($featured_content)) {
				// This is the first post we've added to the featured content
				$new_featured_content[] = $new_featured_post;
			} else {
				$n = count($featured_content);
				$not_pinned = array();

				// Copy pinned elements to the new featured array at apropriate positions
				// and other to the 'not pinned' array
				for ($i = 0; $i < $n; $i++) {
					if ($featured_content[$i]['pinned'] != '') {
						$new_featured_content[$i] = $featured_content[$i];
					} else {
						$new_featured_content[$i] = ''; // we init empty array element so we dont have to do ksort after
						$not_pinned[] = $featured_content[$i];
					}
				}
				// Add $n+1 element to the array
				$new_featured_content[$n] = '';

				// Prepend new post to the 'not pinned' array
				array_unshift($not_pinned, $new_featured_post);

				$j = 0;
				// Fill the new featured content array
				for ($i = 0; $i < $n + 1; $i++) {
					if ($new_featured_content[$i] == '') { // Check if pinned element isn't already there
						$new_featured_content[$i] = $not_pinned[$j];
						$j++;
					}
				}
			}

			pukka_set_option('featured_content', $new_featured_content);
		}

		/**
		 * Removes post from featured content
		 *
		 * @since Pukka 1.0
		 *
		 * @param $post_id Id of the post
		 */
		public function removePost($post_id) {

			// Check if post is already in the featured list, if not do nothing
			if (!$this->isPostFeatured($post_id)) {
				return;
			}

			// Get featured content
			$featured_content = pukka_get_option('featured_content');

			$move_left = false;
			$featured_count = count($featured_content);

			for ($i = 0; $i < $featured_count; $i++) {
				if( $featured_content[$i]['type'] == 'post' && $post_id == $featured_content[$i]['id'] ){
					// post which needs to be removed is found
					unset($featured_content[$i]);
					$new_pos = $i;
					$move_left = true;
				}
				elseif( $move_left == true && $featured_content[$i]['pinned'] != 'on' ){
					// wanted post is already removed, so move other (not pinned) elements
					$featured_content[$new_pos] = $featured_content[$i];

					unset($featured_content[$i]);
					$new_pos = $i;
				}

			}

			ksort($featured_content);
			// array_values clears 'gaps' (which may occur if post at the end is pinned)
			pukka_set_option('featured_content', array_values($featured_content));
		}

		/**
		 * Saving new post with check if it is featured
		 * Note: post meta is actually saved using PukkaMetaBox class
		 *
		 * @since Pukka 1.0
		 *
		 * @param int $post_id ID of the post
		 * @param array $post_meta Array containing post meta
		 */
		public function savePost($post_id, $post_meta) {

			// Only posts have 'Featured' meta box
			if( get_post_type($post_id) != 'post' ){
				return;
			}

			// all meta is already stored
			$is_featured = $post_meta[ PUKKA_POSTMETA_PREFIX .'featured'] == 'on' ? true : false;
			$was_featured = $this->isPostFeatured($post_id);

			// if post is and already was featured do nothing
			if ($is_featured && $was_featured) {
				return;
			}

			if (!$is_featured && $was_featured) {
				$this->removePost($post_id);
			} elseif ($is_featured && !$was_featured) {
				// Prepend post
				$this->prependPost($post_id);
			}
		}

		/* BEGIN: Admin page ****************************/
		/**
		 * Prints content of featured content admin page.
		 * Fetches featured content and prints it to admin page (post, custom, term)
		 *
		 * @since Pukka 1.0
		 *
		 * @global object $post The post object
		 */
		public function printMenuPage() {
			global $post;
			// Get all terms (only categories for now)
			$this->pukka_terms = get_terms(array('category'));
			// Get saved featured content
			$featured_content = pukka_get_option('featured_content');
			?>
			<div id="pukka-wrap" class="wrap">
				<h2><?php _e('Front page manager', 'pukka'); ?></h2>
				<div class="featured-controls">
					<a href="#" id="featured-add-custom" class="pukka-button"><?php _e('Add custom content', 'pukka'); ?></a>
					<a href="#" id="featured-add-post" class="pukka-button"><?php _e('Add post', 'pukka'); ?>
						<input type="text" class="featured-add-post-input" value="" placeholder="Start typing post/page title" autocomplete="off" />
					</a>
					<a href="#" id="featured-add-tax" class="pukka-button"><?php _e('Add category widget', 'pukka'); ?></a>
					<a href="#" id="featured-save" class="pukka-button-primary" ><?php _e('Save', ''); ?></a>

					<img class="waiting" style="display:none;" src="<?php echo admin_url('images'); ?>/wpspin_light.gif" alt="" />
				</div>

				<form id="featured-form">
					<ol id="featured" class="sortable">
			<?php
			if (!empty($featured_content)) {

				// Get all featured post IDs (so we can get all of them with one get_posts)
				$featured_post_ids = array();
				foreach ($featured_content as $featured) {
					if ($featured['type'] == 'post') {
						$featured_post_ids[] = $featured['id'];
					}
				}

				$args = array(
					'posts_per_page' => -1,
					'post__in' => $featured_post_ids,
					'orderby' => 'post__in',
				);

				// Get all featured posts
				//$featured_posts = get_posts($args);

				$i = 0;
				foreach ($featured_content as $featured) {
					$this->printFeaturedBox($featured, true);
				} // foreach( $featured_content as $featured )


				wp_reset_postdata(); // reset postdata when we looped through
			} // if( $featured_content != '' )
			?>
					</ol>
				</form> <!-- #featured-form -->

				<input type="hidden" id="featured-nonce" name="pukka_nonce" value="<?php echo wp_create_nonce('featured_save_nonce'); ?>" />

				<script type="text/javascript">
					var pukka_featured_terms = <?php echo json_encode($this->pukka_terms); ?>,
					pukka_post_edit_url = "<?php echo admin_url('post.php'); ?>";

					jQuery(document).ready(function($){
						// reset form after browser refresh
						$("#featured-form")[0].reset();
					})
				</script>
			</div>
			<?php
		}

		/**
		 * Prints single featured box
		 *
		 * @since Pukka 1.0
		 *
		 * @global object $post Global post object
		 * @param array $featured Array containing attributes of the featured box
		 * @param boolean $print Set to true if you want to print generated html, or false to get html as string
		 * @return string HTML content if $pring is set to false
		 */
		function printFeaturedBox($featured, $print = true) {
			global $post;

			$out = '';
			$data_attrs = '';
			$css_classes = '';
			$box_title = '';

			if ($featured['type'] == 'post') {
				$css_classes .= ' box-' . get_post_meta($featured['id'], PUKKA_POSTMETA_PREFIX .'box_size', true);
				$data_attrs .= 'data-id="' . $featured['id'] . '" data-type="post"';
				$box_title = __('Post', 'pukka');
			} elseif ($featured['type'] == 'term') {
				$css_classes .= ' box-' . $featured['size'];
				if (empty($featured['term_id'])) {
					$featured['term_id'] = $this->pukka_terms[0]->term_id;
				}
				$data_attrs .= 'data-type="term" data-taxonomy="category" data-term_id="' . $featured['term_id'] . '"';
				$box_title = __('Category', 'pukka');
			} else {
				$css_classes .= ' box-' . $featured['size'];
				$data_attrs .= 'data-type="' . $featured['type'] . '"';
				$box_title = __('Custom content', 'pukka');
			}

			$out .= '<li class="box ' . $css_classes . '" ' . $data_attrs . '>
			<div class="featured-box-title">
				<div class="mbox-left-corner"></div>' . $box_title . '<div class="mbox-right-corner"></div>
			</div>
			<div class="featured-box-content">';
			if ($featured['type'] == 'post') {
				$post = get_post($featured['id']); // wp codex: "setup_postdata seems to expects $post variable to be set.", so...
				setup_postdata($post);

				$out .= get_the_post_thumbnail($post->ID, array(75, 75));
				$out .= '<h5>' . get_the_title();
				if('publish' != $post->post_status) {
					$out .= ' <span>(' . $post->post_status . ')</span>';
				}
				$out .= '</h5>
			<span>' . get_the_author() . ', ' . get_the_date() . '</span><br />
			<a href="' . get_permalink() . '">View</a> - <a href="' . admin_url() . 'post.php?post=' . $post->ID . '&action=edit">Edit</a>';
			} elseif ($featured['type'] == 'custom') {

				$out .= '<textarea class="featured-custom-content" rows="3" cols="15">' . $featured['content'] . '</textarea>';
			} elseif ($featured['type'] == 'term') {

				$out .= __('Pick a category: ', 'pukka');
				$out .= '<select class="tax-term pukka-single-select">';
				foreach ($this->pukka_terms as $term) {
					$out .= "\t\t\t\t\t\t" . '<option value="' . $term->term_id . '" ' . selected($featured['term_id'], $term->term_id, false) . '>' . $term->name . '</option>' . "\n";
				}
				$out .= '</select>';
			} // if( $featured['type'] == 'post' )

			$out .= '</div><!-- .featured-box-content -->'
					. $this->featuredBoxMeta($featured) // print box meta: remove, size, pin
					. '</li> <!-- .box -->';

			if( $print ){
				echo $out;
			} else {
				return $out;
			}
		}

		/**
		 * Prints featured box meta, used in printig featured content on admin page
		 *
		 * @since Pukka 1.0
		 *
		 * @param array $featured Array containing attributes of the featured box
		 * @return string generated HTML content
		 */
		public function featuredBoxMeta($featured) {
			$featured_box_size = $featured['type'] != 'post' ? $featured['size'] : get_post_meta($featured['id'], PUKKA_POSTMETA_PREFIX .'box_size', true);
			$out = '';

			$out .= '<span class="featured-size">
			<select class="box-size pukka-single-select">';
			foreach ($this->box_sizes as $box_size) {
				$out .= "\t\t\t\t\t\t" . '<option value="' . $box_size . '" ' . selected($featured_box_size, $box_size, false) . '>' . $box_size . '</option>' . "\n";
			}
			$out .= '</select>
			</span> <!-- .featured-size -->';

			// Add 'banner' checkbox for custom boxes
			if( $featured['type'] == 'custom' ){
				$out .= '<span class="featured-banner">';

				$out .= __('Banner: ', 'pukka') .'<input type="checkbox" class="box-banner" value="on" ';
				if( isset($featured['banner']) && 'on' == $featured['banner'] ){
					$out .= 'checked="checked"';
				}
				$out .= ' />';
				$out .= '</span>';
			}

			$out .= '<span class="featured-box-controls">';

			$out .= '<span class="featured-counter"></span>';
			$out .= '<a href="#" class="featured-remove"></a>';
			$out .= '<span class="featured-pin ';
			if( isset($featured['pinned']) &&  'on' == $featured['pinned']) {
				$out .= ' pinned';
			}
			$out .= '"><input type="checkbox" class="box-pin" value="on" ';
			if( isset($featured['pinned']) && 'on' == $featured['pinned'] ){
				$out .= 'checked="checked"';
			}
			$out .= ' /></span>';

			$out .= '</span> <!-- .featured-box-controls -->';

			return $out;
		}

		/**
		 * AJAX callback method that prints featured box by type
		 *
		 * @global $_POST contains the type of box to be printed
		 */
		public function ajaxGetFeaturedBox() {
			// Get all terms (only categories for now)
			$this->pukka_terms = get_terms(array('category'));

			$out = '';
			$featured = array();
			if (isset($_POST['type'])) {
				switch (trim($_POST['type'])) {
					case 'term': $featured['type'] = 'term';
						break;
					case 'custom': $featured['type'] = 'custom';
						break;
					case 'post': $featured['type'] = 'post';
						$featured['id'] = trim($_POST['id']);
						break;
				}
			}

			$featured['size'] = 'medium';
			$featured['pinned'] = 'no';
			$featured['content'] = '';

			$out .= $this->printFeaturedBox($featured, false);

			echo $out;
			die();
		}

		/**
		 * AJAX callback method that saves featured content
		 * Note: It ads/updates necessary post meta
		 *
		 */
		public function ajaxSaveFeaturedContent() {

			$response['error'] = false;
			$response['message'] = '';

			// Verify this came from the our screen and with proper authorization
			if (!isset($_POST['pukka_nonce']) || !wp_verify_nonce($_POST['pukka_nonce'], 'featured_save_nonce')) {
				$response['error'] = true;
				$response['message'] = __('You do not have sufficient permissions to save these options.', 'pukka');
				echo json_encode($response);
				die();
			}

			$featured_items = isset($_POST['featured_items']) ? json_decode(stripcslashes($_POST['featured_items']), true) : '';
			// Delete all previously saved content
			$this->deleteFeaturedContent();

			// If no content was submited
			if (empty($featured_items)) {
				$response['message'] = __('No featured content submited/content deleted.', 'pukka');
				echo json_encode($response);
				die();
			}

			// Loop through new content and prepare for saving
			$new_content = array();
			foreach ($featured_items as $item) {

				// continue if post box was added but no post selected
				if ($item['type'] == 'post' && (!isset($item['id']) || trim($item['id']) == '')) {
					continue;
				}

				$tmp = array(
					'type' => $item['type'],
					'pinned' => $item['pinned'] ? $item['pinned'] : ''
				);

				// First set box size
				if ($item['type'] == 'post') {
					$tmp['id'] = $item['id'];
					// update post meta
					update_post_meta($item['id'], PUKKA_POSTMETA_PREFIX . 'featured', 'on');
					update_post_meta($item['id'], PUKKA_POSTMETA_PREFIX .'box_size', $item['size']);
				} else {
					$tmp['size'] = $item['size'];
				}

				// Then set content if needed
				if ($item['type'] == 'custom') {
					$tmp['content'] = isset($item['content']) ? $item['content'] : '';
					$tmp['banner'] = isset($item['banner']) ? $item['banner'] : '';
				} elseif ($item['type'] == 'term') {
					$tmp['taxonomy'] = isset($item['taxonomy']) ? $item['taxonomy'] : '';
					$tmp['term_id'] = $item['term_id'];
				}

				$new_content[] = $tmp;
			}

			// Save all
			pukka_set_option('featured_content', $new_content);

			$response['message'] = __('Content updated.', 'pukka');
			echo json_encode($response);
			die(); // this is required to return a proper result
		}

		/**
		 * AJAX callback, used for 'add post' autocomplete feature
		 */
		public function ajaxPostAutoComplete() {
			global $wpdb;

			// Get array of all featured post IDs so post can't be added twice
			$not_post_ids = '';
			if(!empty($_GET['featured_post_ids'])){
				$not_post_ids = isset($_GET['featured_post_ids']) ? explode(',', $_GET['featured_post_ids']) : '';
			}
			$args = array(
				'term' => empty($_GET['term']) ? '' : $_GET['term'],
				'no_post_ids' => $not_post_ids,
				'post_ids' => '',
				'cat' => empty($_GET['cat']) ? '' : $_GET['cat'],
				'meta' => '',
				'lang' => empty($_GET['lang']) ? '' : $_GET['lang'],
				'post_type' => array('post', 'page'),
				'post_status' => 'publish'
			);

			$posts = pukka_get_posts_by($args);
			$list = array();
			foreach($posts as $post){
				$list[] = array(
					'label' => $post->post_title . ' (' . $post->post_type . ')',
					'value' => $post->post_title,
					'ID' => $post->ID,
					'url' => get_permalink($post->ID),
				);
			}

			echo json_encode($list);
			die(); // this is required to return a proper result
		}
		/* END: Admin page ****************************/

	} // Class end
endif; // end if class_exists