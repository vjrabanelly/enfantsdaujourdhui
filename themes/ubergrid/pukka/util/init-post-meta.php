<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');
	
	$meta_boxes = array();

	// Post meta boxes
	$meta_boxes[] = array(
		'id' => 'post_meta',
		'title' => 'Featured',
		'post_type' => 'post',
		'context' => 'side',
		'priority' => 'high',

		'fields' => array(
			array(
				'title' => 'Featured post?',
				'desc' => '',
				'id' => PUKKA_POSTMETA_PREFIX . 'featured',
				'type' => 'checkbox',
			),
			array(
				'title' => 'Box size',
				'desc' => '',
				'id' => PUKKA_POSTMETA_PREFIX . 'box_size',
				'type' => 'select',
				'options' => array('big' => 'big', 'medium' => 'medium', 'small' => 'small'),
			),
			array(
				'title' => 'Secondary image',
				'desc' => '',
				'id' => PUKKA_POSTMETA_PREFIX . 'secondary_image_id',
				'type' => 'file',
				'css_classes' => 'pukka-side-meta',
			),
			array(
				'title' => 'Secondary image URL',
				'desc' => '',
				'id' => PUKKA_POSTMETA_PREFIX . 'secondary_image_url',
				'type' => 'text',
				'css_classes' => 'pukka-side-meta',
			),
		)
	);
	
	$meta_boxes[] = array(
		'id' => 'meta_box_formats',
		'title' => 'Post Formats',
		'post_type' => 'post',
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
			array(
				'title' => __('Media URL', 'pukka'),
				'desc' => __('Youtube, vimeo, dailymotion, soundcloud.. <br /> (complete list: http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F)', 'pukka'),
				'id' => PUKKA_POSTMETA_PREFIX . 'media_url',
				'type' => 'text',
				'std' => ''
			),
			
			array(
				'title' => __('Media embed code', 'pukka'),
				'desc' => __('Enter here embed code from any other media service. Width should be set to 695px. <br />(For YouTube put <b>?wmode=transparent</b> after youtube link, 
					e.g. http://www.youtube.com/watch?v=cZUiMixMMz<b>?wmode=transparent</b>)', 'pukka'),
				'id' => PUKKA_POSTMETA_PREFIX . 'media_embed',
				'type' => 'textarea',
				'std' => ''
			),
			
			array(
				'title' => __('Link', 'pukka'),
				'desc' => __('Insert the URL you wish to link to.', 'pukka'),
				'id' => PUKKA_POSTMETA_PREFIX . 'link',
				'type' => 'text',
				'std' => ''
			),
		)
	);

	// Page meta boxes
	$meta_boxes[] = array(
		'id' => 'page_meta',
		'title' => 'Social',
		'post_type' => 'page',
		'context' => 'side',
		'priority' => 'low',

		'fields' => array(
			array(
				'title' => 'Enable social buttons?',
				'desc' => '',
				'id' => PUKKA_POSTMETA_PREFIX . 'enable_share',
				'type' => 'checkbox',
			),
		)
	);