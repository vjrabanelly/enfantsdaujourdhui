<?php get_header(); ?>

			<?php if ( have_posts() ) : ?>

					<div id="content" class="clearfix">
						<?php /* Start the Loop */ ?>
						<?php while ( have_posts() ) : the_post(); ?>
							<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

								<?php /* Print article media */ ?>
								<?php if( in_array(get_post_format(), array('video', 'audio', 'gallery', 'link')) || has_post_thumbnail() ) : ?>
									<div class="featured">
									<?php
										if( has_post_format('video') || has_post_format('audio') ){
											// print video/sound media
											pukka_media();
										}
										elseif( has_post_format('gallery') ){
											// create gallery
											$args = array(
												'post_type' => 'attachment',
												'numberposts' => -1,
												'exclude' => get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'secondary_image_id', true), // dont display secondary image
												'post_status' => null,
												'post_parent' => $post->ID
											);

											$attachments = get_posts($args);

											if( $attachments ){
												echo '<div class="slider">';
												echo '<ul class="slides gallery-preview">';
												$thumb_list = '';
												foreach ( $attachments as $attachment ) {
													$thumb_list .= '<li>';

													$img = wp_get_attachment_image_src($attachment->ID, 'thumb-single');
													$thumb_list .= '<img src="'.  $img[0] .'" width="'. $img[1] .'" height="'. $img[2] .'" alt="'. esc_attr($attachment->post_title) .'"/>';
													$thumb_list .= '</li>' ."\n";
												}

												echo $thumb_list;

												echo '</ul> <!-- .slides -->';
												echo '</div> <!-- .slider -->';
											}
											echo '<span class="stripe"></span>';
										}
										elseif( has_post_format('link') ) {
											// Print article image and link to external URL
											echo '<a href="'. get_post_meta($post->ID, PUKKA_POSTMETA_PREFIX .'link',true) .'" target="_blank">';
											the_post_thumbnail('thumb-single');
											echo '</a>';
											echo '<span class="stripe"></span>';
										}
										elseif( has_post_thumbnail() ){
											// Just post thumbnail
											the_post_thumbnail('thumb-single');
											echo '<span class="stripe"></span>';
										}
									?>
									</div> <!-- .featured -->
								<?php endif; // if( has_post_format(array('video', 'audio', 'gallery')) || has_post_thumbnail() ) : ?>

								<div class="content-wrap">
									<header>
										<h1 class="entry-title page-title"><?php the_title(); ?></h1>
									</header>

									<div class="entry-meta">
									   <?php pukka_entry_meta(); ?>
									</div> <!-- .entry-meta -->

									<div class="entry-content">
										<?php the_content(); ?>
										<?php wp_link_pages('before=<p class="pagelink">Pages: &after=</p>'); ?> 
									</div><!-- .entry-content -->

									<?php pukka_after_content(); ?>

								</div> <!-- .content-wrap -->
						</article>

						<?php comments_template(); ?>
					<?php endwhile; ?>

					</div><!-- #content -->

			<?php endif; ?>

<?php get_sidebar('right'); ?>
<?php get_footer(); ?>