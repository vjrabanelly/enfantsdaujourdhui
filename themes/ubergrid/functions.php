<?php
/* Load the core theme framework. */
include_once( get_template_directory() . '/pukka/init.php');

if( !isset($content_width) ){
	$content_width = 615;
}

if ( !function_exists('pukka_theme_setup') ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * @since	Pukka 1.0
 */
function pukka_theme_setup(){

	/*
	 * Make theme available for translation.
	*/
	load_theme_textdomain('pukka', get_template_directory() . '/languages');

	$locale = get_locale();
	$locale_file = get_template_directory() . "/languages/$locale.php";
	if ( is_readable( $locale_file ) ){
		require_once( $locale_file );
	}


	// Enable support for Post Thumbnails
	add_theme_support('post-thumbnails');

	// Add Post Thumbnails Sizes
	add_image_size('thumb-single', 695, 9999, false); // single page
	add_image_size('thumb-single-full', 930, 9999, false); // single page (full width)

	$box_options = pukka_fp_box_settings();
	//add_image_size('thumb-content', 615, 9999, false); // single page
	add_image_size('thumb-brick-big', $box_options['big_img_width'], $box_options['big_img_height'], true); // big brick
	add_image_size('thumb-brick-medium', $box_options['medium_img_width'], $box_options['medium_img_height'], true); // medium brick
	add_image_size('thumb-brick-small', $box_options['small_img_width'], $box_options['small_img_height'], true); // small brick

	// Register menu location
	register_nav_menu('primary', __('Primary Menu', 'pukka'));
	register_nav_menu('secondary', __('Secondary Menu', 'pukka'));

	// Add default posts and comments RSS feed links to head
	add_theme_support('automatic-feed-links');

	// Add post formats
	add_theme_support('post-formats', array('video', 'audio', 'gallery', 'link', 'quote'));

	// Custom background support
	add_theme_support('custom-background', array('default-color' => 'c0c9cc'));

	// disable default wp gallery styling
	add_filter( 'use_default_gallery_style', '__return_false' );
}
add_action('after_setup_theme', 'pukka_theme_setup');
endif; // if( function_exists('pukka_theme_setup' )


/**
* Add excerpt to pages
* Used for front page content
*/
function pukka_add_excerpts_to_pages() {
	add_post_type_support('page', 'excerpt');
}
add_action('init', 'pukka_add_excerpts_to_pages');



/**
* Standard wp_title stuff
*/
function pukka_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'pukka' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'pukka_wp_title', 10, 2 );


if ( !function_exists('pukka_scripts') ) :
function pukka_scripts(){

	// Main stylesheet
	wp_enqueue_style('pukka-style', get_stylesheet_uri());

	// Fonts
	wp_enqueue_style('google-roboto-font', '//fonts.googleapis.com/css?family=Roboto:400,300,700&subset=latin,latin-ext,cyrillic');

	// Add icomoon font, used in the main stylesheet
	wp_enqueue_style('icomoon', get_template_directory_uri() . '/fonts/icomoon/style.css', array());

	// Front page and responsive sidebar
	wp_enqueue_script('jquery-masonry');

	// Adds JavaScript to pages with the comment form to support sites with
	// threaded comments (when in use).
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	// Lightbox
	wp_enqueue_script('jquery.swipebox', get_template_directory_uri() . '/js/swipebox/jquery.swipebox.js', array('jquery'));
	wp_enqueue_style('swipebox-style', get_template_directory_uri() . '/js/swipebox/swipebox.css');

	// Slider (used for gallery post format)
	wp_enqueue_script('jquery.flexslider', get_template_directory_uri() . '/js/jquery.flexslider-min.js', array('jquery'));

	// Main theme's JS
	wp_register_script('pukka-script', get_template_directory_uri() . '/js/pukka.js', array('jquery'));
	wp_enqueue_script('pukka-script');

	// Modernizr 
	wp_enqueue_script('modernizr', get_template_directory_uri() .'/js/modernizr.custom.js');

	// get all data necessary for displaying grid on category, tag and date archive pages
	$grid_params = pukka_get_grid_params();

	wp_localize_script('pukka-script', 'Pukka', array(
													'ajaxurl' => admin_url('admin-ajax.php'),
													'grid_layout' => $grid_params,
												)
	);
}
add_action('wp_enqueue_scripts', 'pukka_scripts', 0);
endif; // if( function_exists('pukka_scripts' )


/**
 * Registers two widget areas.
 *
 * @since Pukka 1.0
 *
 * @return void
 */
function pukka_widgets_init() {
		register_sidebar( array(
				'name'          => __( 'Main Widget Area', 'pukka' ),
				'id'            => 'sidebar-1',
				'description'   => __( 'Appears on posts and pages in the sidebar.', 'pukka' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
		) );

		register_sidebar( array(
				'name'          => __( 'Secondary Widget Area', 'pukka' ),
				'id'            => 'sidebar-2',
				'description'   => __( 'Appears on posts and pages in the sidebar.', 'pukka' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
		) );
}
add_action( 'widgets_init', 'pukka_widgets_init' );

/**
* Admin bar link
* 
*/
add_action( 'admin_bar_menu', 'pukka_toolbar', 999 );
function pukka_toolbar( $wp_admin_bar ) {

	//Parent node
	$wp_admin_bar->add_node(array(
								'id'    => 'pukka_theme_settings',
								'title' => __('Theme settings', 'pukka'),
								'href'  => admin_url('themes.php?page=pukka_theme_settings_page'),
							)
					);

	// Child node
	$wp_admin_bar->add_node(array(
								'id'    => 'pukka_front_page_manager',
								'title' => __('Front page manager', 'pukka'),
								'href'  => admin_url('themes.php?page=pukka_front_page_manager'),
								'parent' => 'pukka_theme_settings',
								)
							);
}

/**
 * Removes wp default "[...]" string at the end of excerpt
 *
 * @since Pukka 1.0
 *
 * @global $post Global post object
 * @param string $more
 * @return string
 */
function pukka_excerpt_more($more) {
	global $post;
	return '';
}
add_filter('excerpt_more', 'pukka_excerpt_more');


/**
 * Prints site logo image if it exists, else site logo as text or site name.
 *
 * @since Pukka 1.0
 *
 * @param int $maxw image max width
 * @param int $maxh image max height
 * @return void
 */
function pukka_logo($maxw = 200){
	$logo = '';
	//normal image logo
	$img_id = trim(pukka_get_option('logo_img_id'));
	//retina image logo
	$img_id_ret = trim(pukka_get_option('retina_logo_img_id'));

	//normal image
	if(!empty($img_id)){
		$logo_img = wp_get_attachment_image_src($img_id, 'full');
		$w = $logo_img[1];
		$h = $logo_img[2];
		
		// need to check these because of jetpack plugin
		if(!empty($w) && !empty($h)){
			$k = $w / $h; //aspect ratio

			//check if width or height is outside of predefined max width and height
			if($w > $maxw){
				$w = $maxw;
				$h = round($w / $k);
			}
		}else{
			$w = $maxw;
			$h = '';
		}
		$logo = '<img src="'. $logo_img[0] .'" alt="'. get_bloginfo('name') .'" ';
		
		if( !empty($w) && !empty($h) ){
			$logo .= 'width="'. $w .'" height="'. $h .'" ';
		}
		
		$logo .= 'class="';
		if(!empty($img_id_ret)) {
			$logo .= 'has-retina';
		}
		$logo .= '" />';

		//retina image
		if(!empty($img_id_ret)){
			$logo_img_ret = wp_get_attachment_image_src($img_id_ret, 'full');
			$w = round($logo_img_ret[1] / 2);
			$h = round($logo_img_ret[2] / 2);
			if(!empty($w) && !empty($h)){
				$k = $w / $h; //aspect ratio

				//check if width or height is outside of predefined max width and height
				if($w > $maxw){
					$w = $maxw;
					$h = round($w / $k);
				}
			}else{
				$w = $maxw;
				$h = '';
			}
			$logo .= '<img src="'. $logo_img_ret[0] .'" alt="'. get_bloginfo('name') .'" width="'. $w .'" height="'. $h .'" class="is-retina" />';
		}
	}
	elseif( '' != trim(pukka_get_option('text_logo')) ){
		$logo = '<span id="logo-text">' . esc_html(stripslashes(pukka_get_option('text_logo'))) . '</span>';
	}//if all else fails, return set blog name as logo
	else{
		$logo = '<span id="logo-text">' . get_bloginfo('name') . '</span>';
	}

	echo $logo;
}

/**
 * Prints links to social media accounts with aprropriate icons
 *
 * @since Pukka 1.0
 */
if( !function_exists('pukka_social_menu') ) :
function pukka_social_menu($echo = true){
	$out = '';

	if( pukka_get_option('facebook_url') != '' ) : 
		$out .= '<a href="' . pukka_get_option('facebook_url') . '" target="_blank" class="icon-facebook-rounded"></a>';
	endif;

	if( pukka_get_option('twitter_url') != '' ) :
		$out .= '<a href="' . pukka_get_option('twitter_url') . '" target="_blank" class="icon-twitter"></a>';
	endif;

	if( pukka_get_option('youtube_url') != '' ) :
		$out .= '<a href="' . pukka_get_option('youtube_url') . '" target="_blank" class="icon-youtube"></a>';
	endif;

	if( pukka_get_option('soundcloud_url') != '' ) :
		$out .= '<a href="' . pukka_get_option('soundcloud_url') . '" target="_blank" class="icon-soundcloud-rounded"></a>';
	endif;

	if( pukka_get_option('gplus_url') != '' ) : 
		$out .= '<a href="' . pukka_get_option('gplus_url') . '" target="_blank" class="icon-google-rounded"></a>';
	endif; 

	 if( pukka_get_option('vimeo_url') != '' ) : 
		$out .= '<a href="' . pukka_get_option('vimeo_url') . '" target="_blank" class="icon-vimeo-rounded"></a>';
	 endif; 

	 if( pukka_get_option('linkedin_url') != '' ) : 
		$out .= '<a href="' . pukka_get_option('linkedin_url') . '" target="_blank" class="icon-linkedin-rounded"></a>';
	 endif; 

	 if( pukka_get_option('pinterest_url') != '' ) : 
		$out .= '<a href="' . pukka_get_option('pinterest_url') . '" target="_blank" class="icon-pinterest-rounded"></a>';
	 endif; 

	 if( pukka_get_option('picasa_url') != '' ) : 
		$out .= '<a href="' . pukka_get_option('picasa_url') . '" target="_blank" class="icon-picassa-rounded"></a>';
	 endif; 

	 if( pukka_get_option('instagram_url') != '' ) : 
		$out .= '<a href="' . pukka_get_option('instagram_url') . '" target="_blank" class="icon-instagram"></a>';
	 endif; 

	 if( pukka_get_option('tumblr_url') != '' ) : 
		$out .= '<a href="' . pukka_get_option('tumblr_url') . '" target="_blank" class="icon-tumblr-rounded"></a>';
	 endif; 
	 
	  if( pukka_get_option('flickr_url') != '' ) : 
		$out .= '<a href="' . pukka_get_option('flickr_url') . '" target="_blank" class="icon-flickr-rounded"></a>';
	 endif; 

	 if( pukka_get_option('deviantart_url') != '' ) : 
		$out .= '<a href="' . pukka_get_option('deviantart_url') . '" target="_blank" class="icon-deviantart-rounded"></a>';
	 endif; 

	 if( pukka_get_option('dribbble_url') != '' ) : 
		$out .= '<a href="' . pukka_get_option('dribbble_url') .'" target="_blank" class="icon-dribbble-rounded"></a>';
	 endif; 

	 if( pukka_get_option('reddit_url') != '' ) : 
		$out .= '<a href="' . pukka_get_option('reddit_url') . '" target="_blank" class="icon-reddit"></a>';
	 endif;

	 if( pukka_get_option('behance_url') != '' ) : 
		$out .= '<a href="' . pukka_get_option('behance_url') . '" target="_blank" class="icon-behance-rounded"></a>';
	 endif; 

	 if( pukka_get_option('rss_url') != '' ) : 
		$out .= '<a href="' . pukka_get_option('rss_url') . '" target="_blank" class="icon-feed-rounded"></a>';
	 endif;

	if( pukka_get_option('custom_social_links') != '' ){
		$out .= pukka_get_option('custom_social_links');
	}
	
	if($echo){
		echo $out;
	}else{
		return $out;
	}
}
endif; // if( !function_exists('pukka_social_menu') ) :

if ( ! function_exists( 'pukka_paging_nav' ) ) :
/**
 * Displays navigation to next/previous set of posts when applicable.
 *
 * @return void
 */
function pukka_paging_nav() {
	global $wp_query;

	// Don't print empty markup if there's only one page.
	if ( $wp_query->max_num_pages < 2 )
		return;
	?>
	<nav class="navigation paging-navigation" role="navigation">
		<!--<h1 class="screen-reader-text"><?php _e( 'Posts navigation', 'pukka' ); ?></h1>-->
		<div class="nav-links">

			<?php if ( get_next_posts_link() ) : ?>
			<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'pukka' ) ); ?></div>
			<?php endif; ?>

			<?php if ( get_previous_posts_link() ) : ?>
			<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'pukka' ) ); ?></div>
			<?php endif; ?>

		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}
endif;

if( !function_exists('pukka_entry_meta') ) :
/**
 * Prints post meta (such as date, category, author etc.)
 *
 * @since Pukka 1.0
 *
 */
function pukka_entry_meta(){

		// Date
		$format_prefix = '%2$s';
		$date = sprintf( '<span class="date updated icon-clock"><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a></span>',
				esc_url( get_permalink() ),
				esc_attr( sprintf( __( 'Permalink to %s', 'pukka' ), the_title_attribute( 'echo=0' ) ) ),
				esc_attr( get_the_date( 'c' ) ),
				esc_html( sprintf( $format_prefix, get_post_format_string( get_post_format() ), get_the_date() ) )
		);

		echo $date;

		// Post author
		if ( 'on' != pukka_get_option('single_author_site') && 'post' == get_post_type() ) {
				printf( '<span class="author vcard icon-user"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
						esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
						esc_attr( sprintf( __( 'View all posts by %s', 'pukka' ), get_the_author() ) ),
						get_the_author()
				);
		}

		// Categories
		$categories_list = get_the_category_list( __( ', ', 'pukka' ) );
		if ( $categories_list ) {
				echo '<span class="categories-links icon-folder-open">' . $categories_list . '</span>';
		}

		
		$tag_list = get_the_tag_list( '', __( ', ', 'pukka' ) );
		if ( $tag_list ) {
				echo '<span class="tags-links icon-tag">' . $tag_list . '</span>';
		}
}
endif; // if( !function_exists('pukka_entry_meta') ) :


if( !function_exists('pukka_media') ) :
/**
 * Generate embed code for post media.
 *
 * @since Pukka 1.0
 *
 * @uses pukka_get_embeded_media()
 *
 */
function pukka_media(){
	global $post, $pukka_box;

	$width = 695;
	$height = 390;
	/*
	if( has_post_format('audio') ){
		$height = 165; // soundcloud
	}
	else{
		$height = 390;
	}
	*/

	/* there is secondary image use it */
	if( get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'secondary_image_id', true) != '' ){
			// secondary image is uploaded
			$image = wp_get_attachment_image_src(get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'secondary_image_id', true), 'full');

			echo '<a href="'. get_permalink($post->ID) .'" title="'. esc_attr(get_the_title()) .'"><img src="'. $image[0] .'" width="'. $image[1] .'" height="'. $image[2] .'" alt="'. get_the_title() .'" /></a>' ."\n";
	}
	elseif( get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'media_url', true) != '' || get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'media_embed', true) != ''){
		if( get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'media_url', true) != '' ){
			echo pukka_get_embeded_media(get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'media_url', true), $width, $height);
		}
		else{
			echo do_shortcode(get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'media_embed', true));
		}
		// this should be printed only if there is media
		echo '<span class="stripe"></span>';
	 }
}
endif; // if( !function_exists('pukka_media') ) :

/**
 * Gets embed code for media
 *
 * @since Pukka 1.0
 *
 * @uses wp_oembed_get()
 *
 * @param string $media_url The format of URL that this provider can handle.
 * @param string $width Embedded media width.
 * @param boolean $height Embedded media height.
 */
function pukka_get_embeded_media($media_url, $width=695, $height=390){

		$args = array(
					'width'=> $width,
					//'height' => $height, //it looks like width will be downsized in aspect to height value
				);
		return wp_oembed_get($media_url, $args);
}

/**
* Attach lightbox to post galleries
*
*/
function pukka_gallery_swipebox($content){

	// add checks if you want to add prettyPhoto on certain places (archives etc).

	return str_replace("<a", "<a class='swipebox'", $content);
}
add_filter('wp_get_attachment_link', 'pukka_gallery_swipebox');


/**
*  Attach lightbox to single image
* (or arbitrary content which has only links to images)
*
* @param string $content Content with links to images
* @param string $gallary_id Something unique so multiple galleries on the same page don't get mixed
*
*/
function pukka_attach_swipebox($content, $gallery_id=false){
	$rel_value = 'gallery';

	if( $gallery_id ){
		$rel_value .= '-'. $gallery_id;
	}

	return str_replace('<a', '<a class="swipebox" rel="'. $rel_value .'"', $content);
}
add_filter('pukka_attach_lightbox', 'pukka_attach_swipebox', 1, 2);


/**
 * Highlights page, in a backend 'Pages' screen,
 * which is set to be used as a site's Front page.
 *
 * @since Pukka 1.0
 */
add_action('admin_head', 'pukka_page_placeholder');
function pukka_page_placeholder(){
	$front_page_id = get_option('page_on_front');
	if( !empty($front_page_id) ){
		echo '<style>#post-'.$front_page_id.' { background-color: #FFFFCC; } #post-'.$front_page_id.' .post-title strong:after { color: #999999; content: "'.__('(This page is a placeholder for front page)', 'pukka').'"; font-size: 11px; font-style: italic; font-weight: normal; text-decoration: none; margin-left: 10px;</style>';
	}
}


/**
 * Setup for custom comments.
 *
 * @since Pukka 1.0
 *
 *
 * @param array $args
 * @return string $args
 */
function pukka_comments($args){
		$commenter = wp_get_current_commenter();
		$req = get_option( 'require_name_email' );
		$aria_req = ( $req ? " aria-required='true'" : '' );

		$user = wp_get_current_user();
		$user_identity = $user->exists() ? $user->display_name : '';
		$required_text = sprintf(' ' . __('Required fields are marked %s', 'pukka'), '<span class="required">*</span>');

		$args = array(
			'id_form'           => 'commentform',
			'id_submit'         => 'submit',
			'title_reply'       => __('Leave a Comment', 'pukka'),
			'title_reply_to'    => __('Leave a Reply to %s', 'pukka'),
			'cancel_reply_link' => __('Cancel Reply', 'pukka'),
			'label_submit'      => __('Post Comment', 'pukka'),

			'comment_field' =>  '<p class="comment-form-comment">' .
				'<textarea id="comment" name="comment" cols="45" rows="8" aria-required="true" placeholder="'. __('Your text', 'pukka') .'">' .
				'</textarea></p>',

			'must_log_in' => '<p class="must-log-in">' .
				sprintf(
					__( 'You must be <a href="%s">logged in</a> to post a comment.' ),
					wp_login_url( apply_filters( 'the_permalink', get_permalink() ) )
				) . '</p>',

			'logged_in_as' => '<p class="logged-in-as">' .
				sprintf(
				__( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', 'pukka' ),
					admin_url( 'profile.php' ),
					$user_identity,
					wp_logout_url( apply_filters( 'the_permalink', get_permalink( ) ) )
				) . '</p>',

			'comment_notes_before' => '<p class="comment-notes">' .
				__( 'Your email address will not be published.', 'pukka') . ( $req ? $required_text : '' ) .
				'</p>',
				/*
			'comment_notes_after' => '<p class="form-allowed-tags">' .
				sprintf(
					__( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s' ),
					' <code>' . allowed_tags() . '</code>'
				) . '</p>',
			*/
			'comment_notes_after' => '',
			'fields' => apply_filters( 'comment_form_default_fields', array(

				'author' =>
					'<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) .
					'" placeholder="'. __( 'Name', 'pukka' ) . ( $req ? ' *' : '' ) .
					'" size="30"' . $aria_req . ' />',

				'email' =>
					'<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) .
					'" placeholder="'. __( 'Email', 'pukka' ) . ( $req ? ' *' : '' ) .
					'" size="30"' . $aria_req . ' />',

				'url' =>
					'<input id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) .
					'" placeholder="'.  __( 'Website', 'pukka' ) .
					'" size="30" />'
				)
			),
		);


	return $args;
}
add_filter('comment_form_defaults', 'pukka_comments');

/**
 * Custom Comment Walker class for printing comments
 *
 * @since Pukka 1.0
 *
 */
class Pukka_Walker_Comment extends Walker_Comment {

	protected function comment( $comment, $depth, $args ) {
		if ( 'div' == $args['style'] ) {
			$tag = 'div';
			$add_below = 'comment';
		} else {
			$tag = 'li';
			$add_below = 'div-comment';
		}
?>
		<<?php echo $tag; ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> id="comment-<?php comment_ID(); ?>">
		<?php if ( 'div' != $args['style'] ) : ?>
		<div id="div-comment-<?php comment_ID(); ?>" class="comment-body">
		<?php endif; ?>
		<div class="comment-author vcard">
			<?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
			<?php printf( __( '<cite class="fn">%s</cite> <span class="says">says:</span>' ), get_comment_author_link() ); ?>
		</div>
		<?php if ( '0' == $comment->comment_approved ) : ?>
		<em class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.', 'pukka') ?></em>
		<br />
		<?php endif; ?>

		<div class="comment-meta commentmetadata"><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
			<?php
				/* translators: 1: date, 2: time */
				printf( __('%1$s at %2$s', 'pukka'), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __('(Edit)', 'pukka'), '&nbsp;&nbsp;', '' );
			?>
		</div>
		<div class="comment-text-wrap">
			<?php comment_text() ?>
		</div>
		<div class="reply">
			<?php comment_reply_link( array_merge( $args, array( 'add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
		</div>
		<?php if ( 'div' != $args['style'] ) : ?>
		</div>
		<?php endif; ?>
<?php
	}

	function start_el( &$output, $comment, $depth = 0, $args = array(), $id = 0 ) {
		$depth++;
		$GLOBALS['comment_depth'] = $depth;
		$GLOBALS['comment'] = $comment;

		if ( !empty( $args['callback'] ) ) {
			call_user_func( $args['callback'], $comment, $args, $depth );
			return;
		}

		if ( ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) && $args['short_ping'] ) {
			$this->ping( $comment, $depth, $args );
		} elseif ( 'html5' === $args['format'] ) {
			$this->html5_comment( $comment, $depth, $args );
		} else {
			$this->comment( $comment, $depth, $args );
		}
	}
}

function row1_buttons($buttons) {
	//Remove the format dropdown select
	$remove = array('justifyleft', 'justifyright');
	return array_diff($buttons, $remove);
}
add_filter('mce_buttons_1','row1_buttons');

function row2_buttons($buttons) {
	//Remove the format dropdown select
	$remove = array('formatselect', 'justifycenter');
	return array_diff($buttons, $remove);
}
add_filter('mce_buttons_2','row2_buttons');

function row3_buttons($buttons) {
  return ['fontselect', 'fontsizeselect', 'styleselect', 'backcolor', 'charmap'];
}
add_filter("mce_buttons_3", "row3_buttons");
