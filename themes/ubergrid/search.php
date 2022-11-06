<?php get_header(); ?>

		<div id="primary">
			<div id="content" role="main">

			<?php if ( have_posts() ) : ?>

				<header class="archive-header content-wrap page-header">
					<h1 class="archive-title"><?php printf( __( 'Search Results for: %s', 'pukka' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
				</header>
				
				<?php /* Start the Loop */ ?>
				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', 'search' ); ?>

				<?php endwhile; ?>

				<?php pukka_paging_nav(); ?>
				
			<?php else : ?>
				<?php get_template_part( 'content', 'none' ); ?>
			<?php endif; ?>

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_sidebar('right'); ?>
<?php get_footer(); ?>