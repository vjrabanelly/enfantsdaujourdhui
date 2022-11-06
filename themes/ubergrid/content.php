	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if( in_array(get_post_format(), array('video', 'audio', 'gallery', 'link')) || has_post_thumbnail() ) : ?>
		<div class="featured">
		<?php
			if( has_post_format('video') || has_post_format('audio')){
				pukka_media();
			}
			/*
			elseif( has_post_format('gallery') ){
				echo '<a href="' . get_permalink() . '">';
				the_post_thumbnail('thumb-single');
				echo '</a>';
			}
			*/
			elseif( has_post_format('link') ) {
				echo '<a href="'. get_post_meta($post->ID, '_pukka_link',true) .'" target="_blank">';
				the_post_thumbnail('thumb-single');
				echo '</a>';
			}
			elseif( has_post_thumbnail() ){
				echo '<a href="' . get_permalink() . '">';
				the_post_thumbnail('thumb-single');
				echo '</a>';
			}
		?>
		<span class="stripe"></span>
		</div> <!-- .featured -->
	<?php endif; //<?php if( has_post_format(array('video', 'audio', 'gallery')) || has_post_thumbnail() ) : ?>

	<div class="content-wrap">
		<h1 class="entry-title page-title">
			<?php if( has_post_format('link') ) : ?>
			<a href="<?php echo get_post_meta($post->ID, '_pukka_link',true); ?>" target="_blank">
			<?php else : ?>
			<a href="<?php the_permalink(); ?>">
			<?php endif; ?>
			<?php the_title(); ?>
		</a>
		</h1>
        <div class="entry-meta">
           <?php pukka_entry_meta(); ?>
        </div> <!-- .entry-meta -->
		<div class="entry-content">
			<?php the_content(); ?>
		</div><!-- .entry-content -->
	</div> <!-- .content-wrap -->
</article>