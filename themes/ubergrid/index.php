<?php
	if( is_front_page() && 'on' == pukka_get_option('use_fp_grid') ){
		get_template_part('page-templates/template', 'homepage');
	}else{

	get_header(); ?>

				<div id="content" role="main">

				<?php if ( have_posts() ) : ?>


					<?php /* Start the Loop */ ?>
					<?php while ( have_posts() ) : the_post(); ?>

						<?php get_template_part( 'content', get_post_format() ); ?>

					<?php endwhile; ?>

					<?php pukka_paging_nav(); ?>
					
				<?php else : ?>
					<?php get_template_part( 'content', 'none' ); ?>
				<?php endif; ?>

				</div><!-- #content -->

	<?php get_sidebar('right'); ?>
	<?php get_footer(); 

}