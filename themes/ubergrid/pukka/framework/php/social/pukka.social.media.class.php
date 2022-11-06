<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');
/**
* Social media class
* Implements social media buttons ONLY on 'single' and 'page' pages
* It's intended ONLY to be used on those pages
*
* @todo: make separate class for each social media
*
*/

if (!class_exists('PukkaSocialMedia')) :

	class PukkaSocialMedia {

		private $og_desc_length;
		/**
		 * Class constructor
		 *
		 * @since Pukka 1.0
		 *
		 * @param array $theme_option_pages Theme options pages and options for each page
		 */
		public function __construct($args) {

			$this->og_desc_length = isset($args['og_desc_length']) ? $args['og_desc_length'] : 220;

			// Print Open Graph Tags (if enabled)
			if( pukka_get_option('print_og_tags') == 'on' ){
				add_action('wp_head', array(&$this, 'printOpenGraphTags'), 10);
			}
			
			// Add facebook and twitter js
			add_action('pukka_after_body', array(&$this, 'printSocialScripts'), 10);

			// Add google js to footer
			add_action('wp_print_footer_scripts', array(&$this, 'addGooglePlusScript'), 10);

			// Add pinterest js to footer
			add_action('wp_print_footer_scripts', array(&$this, 'addPinterestScript'), 10);

			// Print social buttons
			add_action('pukka_after_content', array(&$this, 'printSocialButtons'), 10);
		}

		/**
		* Gets description for aprropriate object, optionally removes html tags
		* 
		* @param bool $strip_tags whether to strip tags or not
		* 
		* @return string
		*
		* @note: this method is different from one in the theme class (uses excerpt not content)
		*/
		public function getMetaDescription($strip_tags=true){
			global $post;

			$description = '';

			if( !is_front_page() && (is_single() || is_page()) ){
				$description = apply_filters('excerpt', $post->post_excerpt);
			}
			elseif( is_category() ){
				$description = category_description();
			}
			elseif( is_tag() ){
				$description = tag_description();
			}
			elseif( is_home() || is_front_page() ){
				$description = get_bloginfo('description');
			}

			if( !empty($description) && $strip_tags ){
				// strip all tags and left over line breaks and white space characters
				$description = wp_strip_all_tags($description, true);
			}

			return $description;
		}

		/*
		* Generate and print Open Graph meta tags
		*/
		public function printOpenGraphTags(){
			global $post;

			// og_site_name
			$og_site_name = get_bloginfo('name');

			// og_title
			$og_title = wp_title( '|', false, 'right' );

			// og_url
			if( is_single() || is_page() ){
				$og_url = get_permalink();
			}

			// og_type
			if( !is_front_page() && !is_home() && (is_singular() || is_page()) ){
				$og_type = "article";
			}
			else{
				$og_type = "website";
			}

			// og_image
			$og_image = '';
			if( (is_single() || is_page()) && has_post_thumbnail() ){
				$image = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()),'full');
				$og_image = $image[0];
			}
			else{
				// display site logo
				$logo_id = trim(pukka_get_option('logo_img_id'));

				if( !empty($logo_id) ){
					$logo_image = wp_get_attachment_image_src($logo_id, 'full');
					$og_image = $logo_image[0];
				}
			}


			// og_description
			$og_description = $this->getMetaDescription();
			if( strlen($og_description) > $this->og_desc_length ){
				$og_description = mb_substr($og_description, 0, $this->og_desc_length, 'UTF-8') .'...';
			}

			$out = '';
			/*
			if( pukka_get_option('facebook_admins') != '' ){
				$out .= '<meta property="fb:admins" content="'. pukka_get_option('facebook_admins') .'" />' . "\n";
			}
			*/

			if( pukka_get_option('facebook_app_id') != '' ){
				$out .= '<meta property="fb:app_id" content="'. esc_attr(pukka_get_option('facebook_app_id')) .'" />' . "\n";
			}

			$out .= '<meta property="og:site_name" content="'. esc_attr($og_site_name) .'" />' . "\n";;
			$out .= '<meta property="og:type" content="'. esc_attr($og_type) .'" />' . "\n";

			$out .= '<meta property="og:title" content="'. esc_attr($og_title) .'"/>'. "\n";
			$out .= '<meta property="og:image" content="'. esc_attr($og_image) .'"/>'. "\n";
			$out .= '<meta property="og:description" content="'. esc_attr($og_description) .'" />'. "\n";

			if( is_single() || is_page() ){
				$out .= '<meta property="og:url" content="'. esc_attr($og_url) .'"/>' . "\n";
			}

			echo $out;
		}

		/**
		 * Printing data after <body> tag is opened
		 * Facebook, twitter scripts are set here
		 *
		 * @since Pukka 1.0
		 */
		public function printSocialScripts() {
			global $post;

			// Social media buttons are displayed only on 'single'
			if( is_archive() || is_front_page() || is_page() ){
				return;
			}

			$out = '';

			// Twitter support
			if( pukka_get_option('social_twitter') == 'on' ){
				$out .= '<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\'://platform.twitter.com/widgets.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'twitter-wjs\');</script>';
			}

			// Facebok support
			if( pukka_get_option('social_fb_like') ){
			$out .= trim(
					'<div id="fb-root"></div>
					<script>(function(d, s, id) {
					  var js, fjs = d.getElementsByTagName(s)[0];
					  if (d.getElementById(id)) return;
					  js = d.createElement(s); js.id = id;
					  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId='. pukka_get_option('fb_app_id') .'";
					  fjs.parentNode.insertBefore(js, fjs);
					}(document, \'script\', \'facebook-jssdk\'));</script>'
				); // trim
			}

			echo $out;
		}

		/* Google plus script, footer
		*
		*/
		public function addGooglePlusScript(){

			// Social media buttons are displayed only on 'single' and 'page' pages
			if( is_archive() || is_front_page() || pukka_get_option('social_gplus') != 'on' ){
				return;
			}
			
			$out = '';

			// Google plus support
			$out .= trim('
			<script type="text/javascript">
			  (function() {
				var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;
				po.src = \'https://apis.google.com/js/plusone.js\';
				var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
			  })();
			</script>');

			echo "\n". $out;
		}

		/* Pinterest script, footer
		*
		*/
		public function addPinterestScript(){

			// Social media buttons are displayed only on 'single' and 'page' pages
			if( is_archive() || is_front_page() || pukka_get_option('social_pinterest') != 'on' ){
				return;
			}

			$out = '<script type="text/javascript" async src="//assets.pinterest.com/js/pinit.js"></script>';

			echo "\n". $out;
		}


		/**
		 * Printing stuff after the_content() is called
		 * Facebook, twitter, google plus, linkedin and pinterest social buttons are printed here
		 *
		 * @since Pukka 1.0
		 */
		public function printSocialButtons() {
			global $post;

			// Social buttons could be globaly disabled for pages
			// But enabled for specific page
			if( is_page() ){
				/*
				$page_share_enabled = get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX . 'enable_share', true) == 'on' ? true : false;
				
				if( pukka_get_option('page_disable_share') == 'on' && !$page_share_enabled ){
					return;
				}
				*/

				// disable social buttons on pages
				return;
			}

			echo '<div class="social-buttons">' ."\n";
			// Facebok like button
			if( pukka_get_option('social_fb_like') == 'on' ){
			?>
				<span class="fb-button">
					<div class="fb-like" data-href="<?php the_permalink(); ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
				</span>
			<?php
			}

			// Tweet button
			if( pukka_get_option('social_twitter') == 'on' ){
			?>
				<span class="tw-button">
					<a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php the_permalink(); ?>">Tweet</a>
				</span>
			<?php
			}

			// Google plus button
			if( pukka_get_option('social_gplus') == 'on' ){
			?>
				<span class="gp-button">
					<div class="g-plusone" data-size="tall" data-annotation="none"></div>
				</span>
			<?php
			}

			// Google plus button
			if( pukka_get_option('social_linkedin') == 'on' ){
			?>
				<span class="in-button">
					<script src="//platform.linkedin.com/in.js" type="text/javascript">lang: en_US</script>
					<script type="IN/Share" data-counter="right"></script>
				</span>
			<?php
			}

			// Google plus button
			if( pukka_get_option('social_pinterest') == 'on' ){
				$description = trim(wp_strip_all_tags(get_the_title()));
				$img = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()),'full');
				$image_src = $img[0];
			?>
				<span class="pin-button">
					<a href="//www.pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink()); ?>&media=<?php echo urlencode($image_src); ?>&description=<?php echo $description; ?>" data-pin-do="buttonPin" data-pin-config="beside">
						<img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_gray_20.png" />
					</a>
				</span>
			<?php
			}

			echo '</div> <!-- .social-buttons -->' ."\n";
		}

	} // Class end

endif; // end if class_exists