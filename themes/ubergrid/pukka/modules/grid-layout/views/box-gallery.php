<?php
/**
 * Template for displaying gallery post format box
 * If you want to change this file copy it to: ubergid/pukka-overrides/grid-layout/views
 * and make your changes there.
 * That way they won't be lost whene theme is updated
 */
?>

<?php
	global $pukka_box;
	$brick_css = 'brick-'. $pukka_box['size'];

	if( get_post_format() ){
		$brick_css .= ' brick-' . get_post_format();
	}
?>
	<div class="brick <?php echo $brick_css; ?>">
		<div class="brick-media">
			<?php if( get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'secondary_image_id', true) != '' 
						|| get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'secondary_image_url', true) != ''
					) :
			?>

			<?php
				// If there is secondary image - use it!
				if( get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'secondary_image_id', true) != '' ){
					// secondary image is uploaded
					$image = wp_get_attachment_image_src(get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'secondary_image_id', true), 'full');
				}
				elseif( get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'secondary_image_url', true) != '' ){
					// secondary image URL is specified
					$image_url = get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'secondary_image_url', true);
					$image = array($image_url);

					$image_info = getimagesize($image_url);
					
					if( $image_info != false ){
						$image[] = $image_info[0]; // width
						$image[] = $image_info[1]; // height
					}
				}

				echo '<a href="'. get_permalink() .'"><img src="'. $image[0] .'" width="'. $image[1] .'" height="'. $image[2] .'" alt="'. get_the_title() .'" /></a>' ."\n";
			?>

			<?php else : ?>

			<?php /* else use slider */ ?>
			<ul class="slides">
				<?php
						$args = array(
						'post_type' => 'attachment',
						'posts_per_page' => -1,
						'exclude' => get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'secondary_image_id', true), // dont display secondary image
						'post_status' => null,
						'post_parent' => $post->ID,
						);
						
						$attachments = get_posts( $args );
						if ( $attachments ) {
								$slide_items = '';
								foreach ( $attachments as $attachment ) {
									 $slide_items .= '<li>';
									 $slide_items .= '<a href="'. wp_get_attachment_url($attachment->ID) .'">';
									 $slide_items .= wp_get_attachment_image($attachment->ID, 'thumb-brick-'. $pukka_box['size']);
									 $slide_items .= '</a>';
									 $slide_items .= '</li>' ."\n";
									}
								// attach lightbox
								$slide_items = apply_filters('pukka_attach_lightbox', $slide_items, $post->ID);
								echo $slide_items;
						}
				 ?>
			</ul> <!-- .slides -->

			<?php endif; ?>
		<span class="stripe"></span>
	</div>
		<div class="brick-content">
				<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<?php pukka_box_content(); ?>
	</div> <!-- .brick-content -->

	<div class="brick-meta-wrap">
	<?php pukka_box_meta(); ?>
	</div> <!-- .brick-meta-wrap -->
</div>  <!-- .brick -->