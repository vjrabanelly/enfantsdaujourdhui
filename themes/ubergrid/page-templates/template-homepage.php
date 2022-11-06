<?php
	/*
	* Template name: Home Page
	*/

	/*
	* This is page template for the Home Page
	*/
?>
<?php get_header(); ?>

<div id="homepage-banner">
  <?php echo get_post(356)->post_content; ?>
</div>

<div id="brick-wrap" class="clearfix">
	<?php 
	if( function_exists('pukka_print_fp_content') ){
			pukka_print_fp_content();
	}
	
	if('on' == pukka_get_option('show_home_right_sidebar')){
		get_sidebar('right'); 
	}
	?>
</div>

<?php get_footer(); ?>