<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');


/**
* Here are all the functions that print grid layout (home, category, tag, date archive pages)
* All backend stuff is done elsewhere (featured.content.class.php)
*/

/**
 * Global variable used to pass box size information between functions
 * and template files included with get_template_part function
 */
$pukka_box = array(
			'size' => 'medium', // default box size
			'rand' => false, // is box size generated randomly (during infinite scroll) or specified using Front page manager
);


if( !function_exists('pukka_get_grid_params') ) :
/**
 * Gets all data necessary for displaying grid layout (for home and category archive pages)
 *
 * @since Pukka 1.2
 *
 * @return array grid_params grid view paramaters
 *
 * @note: this could easily work with tags, custom taxonomies and date archive pages
 */
function pukka_get_grid_params(){

	// set pagination 
	if( get_query_var('paged') ){
		$paged = get_query_var('paged');
	}elseif( get_query_var('page') ){
		$paged = get_query_var('page');
	}else{
		$paged = 1;
	}

	$current_page = '';
	$inner_grid = array('use_inner_grid' => 'off');

	if( is_front_page() ){
		// home
		$current_page = 'home';
	}elseif( pukka_get_option('category_grid_layout') == 'on' ){
		// inner grid mode is 'on'
		$tax = '';
		$term_id = '';
		$date = '';

		// find out which page we are on

		if( is_category() ){
			// category
			$current_page = 'taxonomy';
			$tax = get_queried_object()->taxonomy;
			$term_id = get_queried_object()->term_id;
		}

		// init array
		$inner_grid = array(
						'use_inner_grid' => 'on',
						'tax' => $tax,
						'term_id' => $term_id,
						'date' => $date,
					);
	}

	//infinite scroll check
	$infinite_scroll = pukka_get_option('fp_infinite_scroll') != '' ? pukka_get_option('fp_infinite_scroll') : 'off';

	// categories to dipslay on front page
	$front_page_cats = pukka_get_option('front_page_cats');

	$grid_params = array(
					'infinite_scroll' => $infinite_scroll,
					'infinite_page' => $paged + 1, // we want infinite scroll to load next page
					'infinite_more' => true,
					'current_page' => $current_page,
					'front_page_cats' => $front_page_cats,
					'inner_grid' => $inner_grid,
					);

	return $grid_params;
}
endif; // if( !function_exists('pukka_get_grid_params') ) :

/**
 * Checks if 'Max number of front page boxes' option is set in Theme settings and returns it.
 * If not it returns default max box number set in init.php
 *
 * @since Pukka 1.0
 *
 * @return int Max number of front page boxes which will be printed per call
 */
function pukka_get_fp_box_no(){

	$fp_box_no = intval(pukka_get_option('fp_box_no'));
	if( 0 == $fp_box_no ){
		$fp_box_no = PUKKA_DEFAULT_BOX_NO;
	}

	return $fp_box_no;
}

/**
 * It determines which content needs to be printed
 * and calls appropriate printing functions
 *
 * @since Pukka 1.0
 */
function pukka_print_fp_content(){

	// check if front page manager mode is active and there is some front page content
	if( pukka_use_featured_content() ){
		$pukka_box['rand'] = false;
		$fp_box_no = pukka_get_fp_box_no();

		$featured_content = pukka_get_option('featured_content');

		// remove not published posts (ie drafts)
		$featured_count = count($featured_content);
		for( $i=0; $i<$featured_count; $i++ ){
			if( $featured_content[$i]['type'] == 'post' && get_post_status($featured_content[$i]['id']) != 'publish'){
				unset($featured_content[$i]);
			}
		}

		$featured_content = array_splice($featured_content, 0, $fp_box_no);

		pukka_print_featured_content($featured_content);
	}
	else{
		// print latest posts
		$grid_params = pukka_get_grid_params();

		pukka_print_chrono_grid($grid_params);
	}
}

if( !function_exists('pukka_print_featured_content') ) :
/**
 * Prints passed chunk of featured content
 *
 * @since Pukka 1.0
 */
function pukka_print_featured_content($featured_content){
		global $post, $pukka_box;

		// get post IDs
		$featured_post_ids = pukka_get_fp_post_ids($featured_content);

		$args = array(
					'posts_per_page' => -1,
					'post__in' => $featured_post_ids,
					'post_type' => 'any',
					'post_status' => 'publish',
					'orderby' => 'post__in',
					'no_found_rows' => true,
					'cache_results' => false,
				);
		// get all posts in one go
		$featured_posts = get_posts($args);

		$i = 0;

		foreach( $featured_content as $featured ){

			if( $featured['type'] == 'post' ){
				$post = $featured_posts[$i];
				setup_postdata($post);
				$i++; // increse post iterator

				$pukka_box['size'] = get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'box_size', true) != '' ? get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'box_size', true) : $pukka_box['size'];

				if( get_post_format() ){
					$post_format = '-'. get_post_format();
				}
				else{
					$post_format = '';
				}

				if( locate_template(PUKKA_OVERRIDES_DIR_NAME .'/grid-layout/views/box'. $post_format .'.php') != '' ){
					get_template_part(PUKKA_OVERRIDES_DIR_NAME .'/grid-layout/views/box', get_post_format());
				}
				else{
					get_template_part('pukka/'. PUKKA_MODULES_DIR_NAME .'/grid-layout/views/box', get_post_format());
				}
			}
			elseif( $featured['type'] == 'term' ){

				// returns array of post IDs or wp_error
				$post_ids = get_objects_in_term($featured['term_id'], $featured['taxonomy'], array('order'=>'DESC'));

				if( is_wp_error($post_ids) || empty($post_ids) ){
					continue; // skip foreach
				}

				$post_id = '';

				foreach( $post_ids as $p_id ){
					if( get_post_status($p_id) == 'publish' ){
						$post_id = $p_id;
						break;
					}
				}

				// there aren't published posts in category
				if( empty($post_id) ){
					continue; // skip foreach
				}

				$post = get_post($post_id);

				setup_postdata($post);
				$pukka_box['size'] = $featured['size'];

				if( get_post_format() ){
					$post_format = '-'. get_post_format();
				}
				else{
					$post_format = '';
				}

				if( locate_template(PUKKA_OVERRIDES_DIR_NAME .'/grid-layout/views/box'. $post_format .'.php') != '' ){
					get_template_part(PUKKA_OVERRIDES_DIR_NAME .'/grid-layout/views/box', get_post_format());
				}
				else{
					get_template_part('pukka/'. PUKKA_MODULES_DIR_NAME .'/grid-layout/views/box', get_post_format());
				}
			}
			elseif( $featured['type'] == 'custom' ){?>
				<?php
					$css_class = (isset($featured['banner']) && $featured['banner'] == 'on' ) ? ' brick-custom-banner' : '';
				?>
				<div class="brick brick-custom brick-<?php echo $featured['size'] . $css_class ?>">
				<?php echo do_shortcode($featured['content']); ?>
				</div> <!-- .brick -->
			<?php
			} // if( $featured['type'] == 'post )
		} // end foreach
		wp_reset_postdata(); // reset postdata when we're done
}
endif; // if( !function_exists('pukka_print_featured_content') ) :

if( !function_exists('pukka_print_chrono_grid') ) :
/**
 * Prints grid in chronological order based on params passed
 * Used on grid layout pages: category, tag, date archive and front page (when front page manager is inactive)
 *
 * @since Pukka 1.0
 *
 * @param int $paged Page number
 * @param bool $infinite_scroll Posts are printed using infinite scroll or not
 *
 * @return int total number of posts for $params array
 */
function pukka_print_chrono_grid($params=array(), $first_scroll=true){
	global $post, $pukka_box;

	if( !isset($params['current_page']) ){
		$params['current_page'] = '';
	}

	if( !isset($params['paged']) ){
		$params['paged'] = 1;
	}

	if( $params['current_page'] == 'home' ){
		$per_page = pukka_get_fp_box_no();
		$grid_view = 'front_page';
	}
	else{
		$per_page = get_option('posts_per_page');
		$grid_view = 'inner_grid';
	}


	// if "All" is selected for Front Page Categories, only one element is passed in
	// this array with a empty value. We don't need that element so we need to empty array.
	if(count($params['front_page_cats']) == 1 && empty($params['front_page_cats'][0])){
		$params['front_page_cats'] = array();
	}

	// start building query args
	$args = array(
			'posts_per_page' => $per_page,
			'paged' => $params['paged'],
			'post_type' => array('post'),
			'post_status' => 'publish',
			'suppress_filters' => false, // wpml support
		);


	// find out on which page we are and add appropriate args
	if( $params['current_page'] == 'taxonomy' ){
		// category or tag
		$args['tax_query'] = array(
								array(
									'taxonomy' => $params['tax'],
									'field' => 'id',
									'terms' => $params['term_id'],
								)
							);
	}
	elseif( $params['current_page'] == 'date_archive' ){
		// date archive, expected date format: Y-m-d
		$date = explode('-', $params['date']);

		if( count($date) == 3 ){
			$args['year'] = (int) $date[0];
			$args['monthnum'] = (int) $date[1];
			$args['day'] = (int) $date[2];
		}
		elseif( count($date) == 2 ){
			$args['year'] = (int) $date[0];
			$args['monthnum'] = (int) $date[1];
		}
		else{
			$args['year'] = (int) $date[0];
		}

	}
	elseif( $params['current_page'] == 'home' && !empty($params['front_page_cats']) ){
		// home page
		$args['tax_query'] = array(
								array(
									'taxonomy' => 'category',
									'field' => 'id',
									'terms' => $params['front_page_cats'],
								)
							);

	}

	// modify query args 
	$args = apply_filters('pukka_grid_args', $args);

	// do the query, finally
	$query = new WP_Query($args);

	while( $query->have_posts() ){
		$query->the_post();

		// modify box size if needed
		$pukka_box['size'] = apply_filters('pukka_grid_box_size', $grid_view, $first_scroll);

		if( get_post_format() ){
			$post_format = '-'. get_post_format();
		}
		else{
			$post_format = '';
		}

		if( locate_template(PUKKA_OVERRIDES_DIR_NAME .'/grid-layout/views/box'. $post_format .'.php') != '' ){
			get_template_part(PUKKA_OVERRIDES_DIR_NAME .'/grid-layout/views/box', get_post_format());
		}
		else{
			get_template_part('pukka/'. PUKKA_MODULES_DIR_NAME .'/grid-layout/views/box', get_post_format());
		}

		// Show grid banner if needed
		if( pukka_get_option('ad_grid_banner_show') == 'on' ){
			pukka_grid_banner($query->current_post, $params['paged'], $per_page);
		}
	} // endwhile

	wp_reset_postdata();

	// return total number of pages, used for infinite scroll 'pagination'
	return !empty($query->max_num_pages) ? $query->max_num_pages : 1;
}
endif; // if( !function_exists('pukka_print_chrono_grid') ) :


if( !function_exists('pukka_grid_banner') ) :
/**
 * Prints grid view banner
 * Used on grid view pages (except front page with 'front page manager' active)
 *
 * @since Pukka 1.0
 *
 * @param int $current_post Number of a current post in the loop
 * @param int $posts_per_page Number of posts per page
 * @param int $paged Current page number (pagination)
 */
function pukka_grid_banner($current_post, $paged=1, $posts_per_page=''){

	if( empty($posts_per_page) ){
		$posts_per_page = get_option('posts_per_page');
	}

	// after how many boxes banner should be displayed
	$banner_count = (pukka_get_option('ad_grid_banner_show_after') != '') ? pukka_get_option('ad_grid_banner_show_after') : PUKKA_DEFAULT_BOX_NO;
	// banner box size
	$banner_size = (pukka_get_option('ad_grid_banner_box_size') != '') ? pukka_get_option('ad_grid_banner_box_size') : 'medium';

	// current_post starts from 0
	$current_box_no = $posts_per_page * ($paged-1) + $current_post + 1;

	if( $current_box_no % $banner_count == 0 ){ ?>
		<div class="brick brick-<?php echo $banner_size; ?> brick-banner">
		<?php echo pukka_get_option('ad_grid_banner_content'); ?>
		</div> <!-- .brick -->
	<?php }
}
endif; // if( !function_exists('pukka_grid_banner') ) :

/**
 * Checks if Front page manager mode is active and there is featured content
 *
 * @since Pukka 1.0
 *
 * @return bool
 */
function pukka_use_featured_content(){
	$featured_content = pukka_get_option('featured_content');
	return pukka_get_option('use_fp_manager') == 'on' && is_array($featured_content) && !empty($featured_content);
}

/**
 * Extracts and returns all post ids from passed featured content chunk
 * Optionally checks content of 'term' boxes and ads post's ID, from those boxes, to the returning array
 *
 * @since Pukka 1.0
 *
 * @param array $featured_content Featured content (posts, taxonomy or custom content)
 * @param bool $check_terms Flag for checking 'term' boxes
 *
 * @return array
 */
function pukka_get_fp_post_ids($featured_content, $check_terms=false){

	if( !is_array($featured_content) ){
		return;
	}
	$featured_post_ids = array();

	foreach( $featured_content as $featured ){
		if( !isset($featured['type']) ){
			continue;
		}

		if( $featured['type'] == 'post' ){
			$featured_post_ids[] = $featured['id'];
		}
		elseif( $check_terms && $featured['type'] == 'term' ){
			$post_ids = get_objects_in_term($featured['term_id'], $featured['taxonomy'], array('order'=>'DESC'));
			if( is_wp_error($post_ids) || empty($post_ids) ){
				continue; // skip
			}

			$post_id = '';

			foreach( $post_ids as $p_id ){
				if( get_post_status($p_id) == 'publish' ){
					$post_id = $p_id;
					break;
				}
			}

			// there aren't published posts in category
			if( empty($post_id) ){
				continue; // skip foreach
			}

			$featured_post_ids[] = $post_id;
		}

	}

	return $featured_post_ids;
}

if( !function_exists('pukka_infinite_scroll') ) :
/**
 * Infinite scroll AJAX callback function
 * It determines which content needs to be printed
 * and calls appropriate printing functions
 *
 * @since Pukka 1.0
 */
function pukka_infinite_scroll(){
	global $post, $pukka_box;
	$response['error'] = false;
	$response['message'] = '';
	$response['content'] = '';

	//$post_box_sizes = pukka_set_box_sizes();

	$params = array(
				'paged' => $_GET['page_no'],
				'current_page' => isset($_GET['current_page']) ? $_GET['current_page'] : '',
				'front_page_cats' => isset($_GET['front_page_cats']) ? $_GET['front_page_cats'] : '',
				'tax' => isset($_GET['inner_grid']['tax']) ? $_GET['inner_grid']['tax'] : '',
				'term_id' => isset($_GET['inner_grid']['term_id']) ? (int)$_GET['inner_grid']['term_id'] : '',
				'date' => isset($_GET['inner_grid']['date']) ? $_GET['inner_grid']['date'] : '',
			);

	// get number of boxes that needs to be displayed
	if( $params['current_page'] == 'home' ){
		$per_page = pukka_get_fp_box_no();
	}
	else{
		$per_page = get_option('posts_per_page');
	}

	ob_start(); // start buffer

	if( $params['current_page'] == 'home' && pukka_use_featured_content() ){

		// if front page manager mode is active
		$featured_content = pukka_get_option('featured_content');

		// remove not published posts (ie drafts)
		$featured_count = count($featured_content);
		for( $i=0; $i<$featured_count; $i++ ){
			if( $featured_content[$i]['type'] == 'post' && get_post_status($featured_content[$i]['id']) != 'publish'){
				unset($featured_content[$i]);
			}
		}

		// new count
		$featured_count = count($featured_content);

		// get just the chunk we need
		$featured_content = array_splice($featured_content, ($params['paged']-1) * $per_page, $per_page);

		pukka_print_featured_content($featured_content);
		if( $featured_count > ($params['paged'] * $per_page) ){
			$load_more = true;
		}
		else{
			$load_more = false;
		}
	}
	else{
		// print latest posts
		$max_num_pages = pukka_print_chrono_grid($params, false);

		if( $params['paged'] < $max_num_pages ){
			$load_more = true;
		}
		else{
			$load_more = false;
		}

	}

	$response['content'] = ob_get_clean(); // get buffer content and clear it

	$response['load_more'] = $load_more;
	wp_reset_postdata();

	echo json_encode($response);
	die();
}
add_action('wp_ajax_pukka_infinite_scroll', 'pukka_infinite_scroll');
add_action('wp_ajax_nopriv_pukka_infinite_scroll', 'pukka_infinite_scroll');
endif; // if( !function_exists('pukka_infinite_scroll') ) :

if( !function_exists('pukka_box_content') ) :
/**
 * Prints content for front page boxes
 * Big box:     full excerpt
 * Medium box:  trimmed excerpt
 * Small box:   no content
 */
function pukka_box_content(){
	global $pukka_box;

	if( $pukka_box['size'] == 'big' || has_post_format('quote') ){
		the_excerpt();
	}
	elseif( $pukka_box['size'] == 'medium' ){
		add_filter('excerpt_length', 'pukka_box_excerpt_length', 998);

		the_excerpt();

		remove_filter('excerpt_length', 'pukka_box_excerpt_length', 998);
	}
	elseif( !pukka_box_has_media() ){
		// small box: display content if box doesn't have media
		add_filter('excerpt_length', 'pukka_box_excerpt_length', 998);

		the_excerpt();

		remove_filter('excerpt_length', 'pukka_box_excerpt_length', 998);
	}
}
endif; // if( !function_exists('pukka_box_content') ) :

/**
* Helper function for checking if box is displaying some media
*
* @since Pukka 1.0
* @param int $post_id Post ID
* @return bool
*/
function pukka_box_has_media($post_id=0){

	if( !$post_id ){
		$post_id = get_the_ID();
	}

	if( has_post_thumbnail()
		|| has_post_format('video', $post_id)
		|| has_post_format('audio', $post_id) // buggy together
		|| get_post_meta($post_id, PUKKA_POSTMETA_PREFIX .'secondary_image_id', true) != '' 
		|| get_post_meta($post_id, PUKKA_POSTMETA_PREFIX .'secondary_image_url', true) != '' 
	){
		return true;
	}
	else{
		return false;
	}
}


if( !function_exists('pukka_box_excerpt_length') ) :
/**
* Shorter excerpt, used for printing medium box content
*
*/
function pukka_box_excerpt_length( $length ) {
	return 18;
}
endif; // if( !function_exists('pukka_box_excerpt_length') ) :

if( !function_exists('pukka_get_excerpt') ) :
/**
 * Creates excerpt with custom read more arrow every time the_excerpt is called
 *
 * @since Pukka 1.0
 *
 * @param string $excerpt Excerpt text
 * @return string
 */
function pukka_get_excerpt($excerpt) {
	global $post;

	// dont print 'read more' if excerpt is empty
	if( empty($excerpt) ){
		return;
	}

	if( has_post_format('link') ){
		$url = get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'link', true);
		$target = '_blank';
	}
	else{
		$url = get_permalink($post->ID);
		$target = '_self';
	}

	$read_more = pukka_get_option('read_more') != '' ? pukka_get_option('read_more') : '&rarr;';

	if( !has_post_format('quote') ){
		return $excerpt . '<a class="moretag" href="'. $url . '" title="' . __('Read more', 'pukka') . '" target="'. $target .'"> '. $read_more .' </a>';
	}
	else{
		return $excerpt;
	}
}
add_filter('get_the_excerpt', 'pukka_get_excerpt', 998);
endif; // if( !function_exists('pukka_get_excerpt') ) :


if( !function_exists('pukka_box_meta') ) :
/**
 * Prints post meta content (category and format)
 *
 * @since Pukka 1.0
 */
function pukka_box_meta(){
	global $post;
	$box_size = get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'box_size', true);

	//if( $box_size != 'small' ){

		$categories = get_the_category();
		echo '<span class="brick-meta">';
		if( !empty($categories) ){
			echo '<a href="'. get_category_link($categories[0]->term_id) .'" title="' . esc_attr($categories[0]->name) .'">' . $categories[0]->name . '</a>';
		}
		if('on' == pukka_get_option('show_home_page_date')){
			echo '<span class="date"> &sdot; <a href="'. get_permalink() .'">'. get_the_date() . '</a></span>';
		}
		if( $box_size == 'big' && 'on' == pukka_get_option('show_home_page_comments') ){
			echo '<span>';
			comments_popup_link( __('Leave a comment', 'pukka'), __('1 Comment', 'pukka'), __('% Comments', 'pukka'));
			echo '</span>';
		}
		echo '</span>';
	//}

	$post_format_css = 'brick-format format-'. (get_post_format() != '' ? get_post_format() : 'standard');
	echo '<span class="'. $post_format_css .'"></span>';
}
endif; // if( !function_exists('pukka_box_meta') ) :


if( !function_exists('pukka_box_social') ) :
/**
 * Prints social share buttons
 *
 * @since Pukka 1.0
 */
function pukka_box_social(){
	global $post;

	$url = get_permalink();
	$title = get_the_title();
	$description = wp_strip_all_tags(apply_filters('excerpt', $post->post_excerpt), true);
	$image = '';
	$networks = array('fb' => 'facebook', 'tw' => 'twitter', 'gp' => 'google', 'in' => 'linkedin', 'pt' => 'pinterest');
	$share_html = '';

	if( has_post_thumbnail() ){
		$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
		$image = $thumb[0];
	}

	$data = sprintf(
				'data-url="%s" data-title="%s" data-desc="%s" data-image="%s"',
				esc_attr($url), // data-url
				esc_attr($title), // data-title
				esc_attr($description), // data-desc
				esc_attr($image) // data-image
			);

	foreach( $networks as $k => $v){
		$share_html .= sprintf(
					'<a href="#" class="pukka-share pukka-share-%s icon-%s-rounded" data-network="%s"></a>',
					esc_attr($k), // css class
					esc_attr($v), // css class
					esc_attr($k) // data-network
				);
	}

	echo '<span class="social-arrow"></span><span class="social-label">'. __('share', 'pukka') .'</span>';

	echo '<span class="box-social-buttons" '. $data .'>'. $share_html .'</span>';

}
endif; // if( !function_exists('pukka_box_social') ) :

/**
 * Returns all settings needed for front page box and image sizes
 *
 * @since Pukka 1.0
 *
 * @return array
 */
function pukka_fp_box_settings() {
	$options = array();

	$margin = pukka_get_option('fp_box_margin');
	if (empty($margin) && '0' !== $margin) {
		$margin = 10;
	}
	$options['margin'] = intval($margin);

	$bbox_width = pukka_get_option('fp_basic_box_width');
	if (empty($bbox_width)) {
		$bbox_width = 250;
	}
	$options['box_width'] = intval($bbox_width);


	$bbox_height = pukka_get_option('fp_basic_box_height');
	if (empty($bbox_height)) {
		$bbox_height = 520;
	}
	$options['box_height'] = intval($bbox_height);

	$num_columns = pukka_get_option('fp_content_columns');
	if (empty($num_columns)) {
		$num_columns = 0;
	}
	$options['num_columns'] = intval($num_columns);


	$sidebar_width = pukka_get_option('sidebar_width');
	if (empty($sidebar_width)) {
		$sidebar_width = 250;
	}
	$options['sidebar_width'] = intval($sidebar_width);

	$home_sidebar = pukka_get_option('show_home_sidebar');
	if(!empty($home_sidebar) && 'none' != $home_sidebar){
		$options['home_sidebar'] = true;
	}else{
		$options['home_sidebar'] = false;
	}

	$img_width = pukka_get_option('fp_basic_box_image_width');
	if(empty($img_width)){
		$img_width = 250;
	}

	$img_height = pukka_get_option('fp_basic_box_image_height');
	if (empty($img_height)) {
		$img_height = 290;
	}

	$options['big_img_width'] = intval(($img_width + $margin) * 2);
	$options['big_img_height'] = intval($img_height);

	$options['medium_img_width'] = intval($img_width);
	$options['medium_img_height'] = intval($img_height);

	$options['small_img_width'] = intval($img_width);
	$options['small_img_height'] = intval($img_height / 2 - $margin);

	// content width, this is needed for some wrappers width calculations (like #main on single pages)
	$options['content_width'] = 720;

	return $options;
}


/**
* Sets box size based on page on which box is printed
*
* @param string $view page on which box is printed
*
* @param bool $first_scroll if page is loaded with infinite scroll or not
*
* @return string grid box size
*/
function pukka_grid_box_size($view='', $first_scroll=false){
	global $post, $pukka_box;
	
	$box_size = $default_size = 'medium';

	if( get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'box_size', true) != '' ){
		// If post has box size set - use it
		$box_size = get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'box_size', true);
	}
	else{
		// else set box size depending on the page we're at

		switch( $view ){
			case 'front_page':
				$box_size = pukka_get_option('fp_default_box_size') != '' ? pukka_get_option('fp_default_box_size') : $default_size;
				break;
			case 'inner_grid':
				$box_size = pukka_get_option('inner_grid_default_box_size') != '' ? pukka_get_option('inner_grid_default_box_size') : $default_size;
				break;
		}

		if( $box_size == 'random' ){

			if( $first_scroll ){
				// display 'bigger' boxes more often in first scroll
				$random_box_sizes = array('big', 'big', 'big', 'medium', 'medium');
			}
			else{
				// for content loaded with infinite scroll display 'smaller' boxes more often
				$random_box_sizes = array('big', 'medium', 'medium', 'small', 'small');
			}

			$box_size = $random_box_sizes[rand(0, count($random_box_sizes)-1)];

			$pukka_box['rand'] = true;
		}

	}

	return $box_size;
}
add_filter('pukka_grid_box_size', 'pukka_grid_box_size', 1, 1);
