<div id="menu-strip">
	<header>
	<label id="menu-open" for="check" onclick></label>
	<h1>
		<a href="<?php echo home_url(); ?>">
		<?php if( pukka_get_option('responsive_logo_img_id') ) : ?>
		
		<?php
			// Display logo if it's there
			$img_id = trim(pukka_get_option('responsive_logo_img_id'));
			$logo_img = wp_get_attachment_image_src($img_id, 'full');
		?>
		<img src="<?php echo $logo_img[0] ?>" alt="<?php echo get_bloginfo('name') ?>" />

		<?php else: ?>

		<?php echo get_bloginfo('name'); ?>
		
		<?php endif; ?>
		</a>
	</h1>
	</header>
</div>
<div id="sidebar-bg"></div>
<div id="left-sidebar-wrap">
	<input type="checkbox" id="check" name="check" />
	<div id="sidebar-top">
			<a href="<?php echo home_url(); ?>" id="logo">
				<?php pukka_logo(); ?>
			</a>
	</div> <!-- #sidebar-top -->
	<div id="sidebar-wrap" class="<?php if('on' == pukka_get_option('popup_submenu_enable')){ echo 'popup';} ?>">
		<div id="sidebar">
			<div id="main-menu">
				<?php wp_nav_menu(array('theme_location' => 'primary')); ?>
			</div>
			<div id="secondary-menu">
			<?php wp_nav_menu(array('theme_location' => 'secondary', 'fallback_cb' => '')); ?>
			</div>
			<div id="social-menu" class="clearfix">
				<?php pukka_social_menu(); ?>
			</div> <!-- #social-menu -->
			<?php if( pukka_get_option('copy') != '' ) : ?>
			<span id="copy"><?php echo pukka_get_option('copy'); ?></span>
			<?php endif; 
			
			$hide_flags = pukka_get_option('hide_language_flags');
			if('on' != $hide_flags){
				if(defined('ICL_LANGUAGE_CODE')){
					$languages = icl_get_languages('skip_missing=1&orderby=id&order=asc');
					$lang_html = '<div id="main-lng-switch" class="left">';
					foreach($languages as $lang){
						if(ICL_LANGUAGE_CODE == $lang['language_code']){
							$lang_html .= '<span><img src="' . $lang['country_flag_url'] . '" alt="' . $lang['language_code'] . '" /></span>';
						}
						else{
							$lang_html .= '<a href="' . $lang['url'] . '"  title="' . $lang['native_name'] . '"><img src="' . $lang['country_flag_url'] . '" alt="' . $lang['language_code'] . '" /></a>';
						}
					}
					$lang_html .= '</div>';
					echo $lang_html;
				}
			}
			?>
		</div> <!-- #sidebar -->
	</div> <!-- #sidebar-wrap -->
</div>
