<?php get_header(); ?>

		<div id="content" class="site-content" role="main">

		<?php if ( have_posts() ) : ?>

			<?php
				/* Queue the first post, that way we know
				 * what author we're dealing with.
				 */
				the_post();
			?>
			<div class="content-wrap">
				<header class="archive-header">
					<h1 class="archive-title"><?php echo get_the_author(); ?></h1>
				</header><!-- .archive-header -->
				<?php if ( get_the_author_meta( 'description' ) ) : ?>
					<?php echo get_the_author_meta( 'description' ); ?>
				<?php endif; ?>
			</div> <!--. content-wrap -->

			<?php
				/* Since we called the_post() above, we need to
				 * rewind the loop back to the beginning.
				 */
				rewind_posts();
			?>

			<?php /* The loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', get_post_format() ); ?>
			<?php endwhile; ?>

			<?php pukka_paging_nav(); ?>
		
		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>

		</div><!-- #content -->

<?php get_sidebar('right'); ?>
<?php get_footer(); ?>