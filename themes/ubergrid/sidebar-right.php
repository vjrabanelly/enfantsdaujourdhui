<div id="sidebar-right">

<?php if( is_active_sidebar('sidebar-1') ) : ?>
	<div id="primary" class="sidebar-container" role="complementary">
		<div class="sidebar-inner">
			<div class="widget-area">
				<?php dynamic_sidebar( 'sidebar-1' ); ?>
			</div><!-- .widget-area -->
		</div><!-- .sidebar-inner -->
	</div><!-- #primary -->
<?php endif; ?>

<?php if( is_active_sidebar('sidebar-2') ) : ?>
	<div id="secondary" class="sidebar-container" role="complementary">
		<div class="sidebar-inner">
			<div class="widget-area">
				<?php dynamic_sidebar( 'sidebar-2' ); ?>
			</div><!-- .widget-area -->
		</div><!-- .sidebar-inner -->
	</div><!-- #secondary -->
<?php endif; ?>

</div> <!-- #sidebar-right -->