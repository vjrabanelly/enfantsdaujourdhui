<?php

function pukka_set_dm_array(){
	$dm_type = isset($_POST['_pukka_dynamic_meta_type']) ? $_POST['_pukka_dynamic_meta_type'] : '';
	$dm_size = isset($_POST['_pukka_dynamic_meta_size']) ? $_POST['_pukka_dynamic_meta_size'] : '';
	$dm_title = isset($_POST['_pukka_dynamic_meta_title']) ? $_POST['_pukka_dynamic_meta_title'] : '';
	$dm_content = isset($_POST['_pukka_dynamic_meta_content']) ? $_POST['_pukka_dynamic_meta_content'] : '';

	$dm = array();

	if ($dm_type != '') {
		for ($i = 0; $i < count($dm_type); $i++) {
			$dm[] = array(
				'type' => $dm_type[$i],
				'size' => $dm_size[$i],
				'title' => $dm_title[$i],
				'content' => $dm_content[$i]
			);
		}
	}

	return $dm;
}