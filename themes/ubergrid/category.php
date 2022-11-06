<?php get_header(); ?>

<?php
	global $pukka_box;
	$use_cat_grid_layout = false; // grid layout is turned off by default

	// get current category id
	$category = get_category(get_query_var('cat'));
	$cat_id = $category->cat_ID;

	// is grid layout for categories turned on
	$cat_grid_layout = pukka_get_option('category_grid_layout') == 'on' ? true : false;

	if( $cat_grid_layout ){
		// if so get selected categories
		$grid_cats = pukka_get_option('grid_cats') != '' ? (array)pukka_get_option('grid_cats') : array();

		if( empty($grid_cats) || in_array($cat_id, $grid_cats) ){
			$use_cat_grid_layout = true;
		}
	}
?>

			<?php if( $use_cat_grid_layout ) : ?>
			<?php
				$sidebar_width = pukka_get_option('right_sidebar_width');
				$box_options = pukka_fp_box_settings();
				if(empty($sidebar_width)){
					$sidebar_width = 225;
				}
			?>
			<div id="brick-wrap" class="grid-cat-sidebar">
				<?php get_sidebar('right');  ?>
			<?php else : ?>
			<div id="content">
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
			<header class="archive-header content-wrap <?php if($use_cat_grid_layout){ echo 'brick brick-cat-title'; } ?>">
				<h1 class="archive-title"><?php echo single_cat_title( '', false ); ?></h1>

				<?php if ( category_description() ) : // Show optional category description ?>
				<div class="archive-meta"><?php echo category_description(); ?></div>
				<?php endif; ?>
			</header><!-- .archive-header -->

				<?php /* Start the Loop */ ?>
				<?php while ( have_posts() ) : the_post(); ?>

					<?php
						if( $use_cat_grid_layout ){

							// set box size
							$pukka_box['size'] = apply_filters('pukka_grid_box_size', 'inner_grid');

							// determine which format post has
							if( get_post_format() ){
								$post_format = '-'. get_post_format();
							}
							else{
								$post_format = '';
							}

							// this way so it can be easily overriden
							if( locate_template(PUKKA_OVERRIDES_DIR_NAME .'/grid-layout/views/box'. $post_format .'.php') != '' ){
								get_template_part(PUKKA_OVERRIDES_DIR_NAME .'/grid-layout/views/box', get_post_format());
							}
							else{
								get_template_part('pukka/'. PUKKA_MODULES_DIR_NAME .'/grid-layout/views/box', get_post_format());
							}

							// Show grid banner if needed
							if( pukka_get_option('ad_grid_banner_show') == 'on' ){
								pukka_grid_banner($wp_query->current_post);
							}

						}
						else{
							get_template_part( 'content', get_post_format() );
						}

					?>

				<?php endwhile; ?>

				<?php
					if( !$use_cat_grid_layout ){
						pukka_paging_nav();
					}
				?>
				
			<?php else : ?>
				<?php get_template_part('content', 'none'); ?>
			<?php endif; ?>

			</div><!-- #content / #brick-wrap -->

<?php
if( !$use_cat_grid_layout ){
	get_sidebar('right');
}
?>
<?php get_footer(); ?>