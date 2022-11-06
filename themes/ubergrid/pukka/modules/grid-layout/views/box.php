<?php
/**
 * Template for displaying standard post format box
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
<?php
	$css_class = '';
	// we dont want to use 'secondary image' if box size is random generated
	if( has_post_thumbnail() 
		|| (get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'secondary_image_id', true) != '' 
			|| get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'secondary_image_url', true) != '' 
			&& !$pukka_box['rand']
			)
	)
	: ?>
		<?php
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
		elseif( has_post_thumbnail() ){
			$image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'thumb-brick-'.$pukka_box['size']);
		}
		?>
		<div class="brick-media">
			<a href="<?php the_permalink(); ?>">
			 <?php
				echo '<img src="'. $image[0] .'" width="'. $image[1] .'" height="'. $image[2] .'" alt="'. get_the_title() .'" />' ."\n";
			?>
			</a>
			<span class="stripe"></span>
		</div>
	<?php else :
		$css_class = ' no-media';
	endif; //if( has_post_thumbnai() || get_post_meta($post->ID, '_pukka_secondary_image', true) != '' )
	?>

	<div class="brick-content<?php echo $css_class; ?>">
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php pukka_box_content(); ?>
	</div> <!-- .brick-content -->

	<div class="brick-meta-wrap">
	<?php pukka_box_meta(); ?>
	</div> <!-- .brick-meta-wrap -->
</div>  <!-- .brick -->