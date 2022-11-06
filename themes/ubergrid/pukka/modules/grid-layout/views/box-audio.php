<?php
/**
 * Template for displaying audio post format box
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
		<?php pukka_media(); ?>
	</div>
	<div class="brick-content">
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<?php pukka_box_content(); ?>
	</div> <!-- .brick-content -->

	<div class="brick-meta-wrap">
	<?php pukka_box_meta(); ?>
	</div> <!-- .brick-meta-wrap -->
</div>  <!-- .brick -->