<?php get_header(); ?>

			<div id="content">

			<?php if ( have_posts() ) : ?>
			<header class="archive-header content-wrap">
				<h1 class="archive-title">
					<?php
						if( is_day() ) :
							printf( __('Daily Archives: %s', 'pukka'), get_the_date() );

						elseif( is_month() ) :
							printf( __( 'Monthly Archives: %s', 'pukka' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'pukka' ) ) );

						elseif( is_year() ) :
							printf( __( 'Yearly Archives: %s', 'pukka' ), get_the_date( _x( 'Y', 'yearly archives date format', 'pukka' ) ) );

						else :
							_e('Archives', 'pukka');

						endif;
					?>
				</h1>

			</header><!-- .archive-header -->

				<?php /* Start the Loop */ ?>
				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', get_post_format() ); ?>

				<?php endwhile; ?>

				<?php pukka_paging_nav(); ?>
				
			<?php else : ?>
				<?php get_template_part('content', 'none'); ?>
			<?php endif; ?>

			</div><!-- #content -->

<?php get_sidebar('right'); ?>
<?php get_footer(); ?>