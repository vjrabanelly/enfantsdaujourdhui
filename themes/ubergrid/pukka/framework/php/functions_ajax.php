<?php

/**
 * Autocomplete AJAX callback function
 */
function pukka_autocomplete(){

    check_ajax_referer('pukka_ajax_autocomplete', 'pukka_ajax_nonce');
    // TODO: $_GET array sanitization

    $returnArr = array();

    if( $_GET['type'] == 'post' ){

        $params = array(
            'term' => empty($_GET['term']) ? '' : $_GET['term'], // search query term
            'no_post_ids' => '',
            'post_ids' => '',
            'cat' => empty($_GET['cat']) ? '' : $_GET['cat'],
            'meta' => '',
            'lang' => empty($_GET['lang']) ? '' : $_GET['lang'],
            'post_type' => '',
            'post_status' => 'publish'
        );

        $posts = pukka_get_posts_by($params);
        
        // TODO: pozvati 'ajax_search_autocomplete' (kad se standardizuje)
        foreach( $posts as $post ){
            $returnArr[] = array(
                'value' => $post->ID,
                'label' => $post->post_title,
                'url' => get_permalink($post->ID),
            );
        }
    }
    elseif( $_GET['type'] == 'term' ){
        $name_like = !empty($_GET['term']) ? $_GET['term'] : ''; // search query term
        $taxonomy = !empty($_GET['source']) ? $_GET['source'] : '';

        // TODO: add empty values check
        $terms = pukka_get_like_terms($taxonomy, $name_like);

        if( $terms !== false ){
            foreach( $terms as $term ){
                $returnArr[] = array(
                            'value' => $term->term_id,
                            'label' => $term->name,
                            'url' => get_term_link((int)$term->term_id, $taxonomy),
                        );
            }
        }
    }

    echo json_encode($returnArr);
    die();
}
add_action('wp_ajax_pukka_ajax_autocomplete', 'pukka_autocomplete');
add_action('wp_ajax_nopriv_pukka_ajax_autocomplete', 'pukka_autocomplete');



/**
 * AJAX callback, used for search autocomplete
 */
function ajax_search_autocomplete() {
	global $wpdb;

	$args = array(
		'term' => empty($_GET['term']) ? '' : $_GET['term'],
		'no_post_ids' => '',
		'post_ids' => '',
		'cat' => empty($_GET['cat']) ? '' : $_GET['cat'],
		'meta' => '',
		'lang' => empty($_GET['lang']) ? '' : $_GET['lang'],
		'post_type' => '',
		'post_status' => 'publish'
	);

	$posts = pukka_get_posts_by($args);
	$list = array();
	foreach($posts as $post){
		$list[] = array(
			'label' => $post->post_title . ' (' . $post->post_type . ')',
			'value' => $post->post_title,
			'url' => get_permalink($post->ID),
		);
	}

	die(json_encode($list));
}

add_action('wp_ajax_pukka_search_autocomplete', 'ajax_search_autocomplete');
add_action('wp_ajax_nopriv_pukka_search_autocomplete', 'ajax_search_autocomplete');


/**
 * Search term using LIKE '%string%' query against the term name only
 *
 * @since Pukka 1.2.1
 *
 * @param $taxonomy string
 * @param $name_lie string
 * @param $language string
 *
 * @return array Array of term objects
 */
function pukka_get_like_terms($taxonomy='category', $name_like='', $languge=''){

    if( empty($name_like) ){
        return false;
    }

    // If WPML plugin is active and currently active language is not default one
    if( defined('ICL_LANGUAGE_CODE') && !empty($language) && ICL_LANGUAGE_CODE != $language ){
        global $sitepress;
        $sitepress->switch_lang($language);
    }

    $args = array(
                'orderby'       => 'name', 
                'order'         => 'ASC',
                'hide_empty'    => false, 
                'exclude'       => array(), 
                'exclude_tree'  => array(), 
                'include'       => array(),
                'number'        => '', 
                'fields'        => 'all', 
                'slug'          => '', 
                'parent'         => '',
                'hierarchical'  => true, 
                'child_of'      => 0, 
                'get'           => '', 
                'name__like'    => $name_like,
                'pad_counts'    => false, 
                'offset'        => '', 
                'search'        => '', 
                'cache_domain'  => 'core'
            );

    $terms = get_terms($taxonomy, $args);

    // Switch language back to default
    if( defined('ICL_LANGUAGE_CODE') && !empty($language) && ICL_LANGUAGE_CODE != $language ){
        global $sitepress;
        $sitepress->switch_lang(ICL_LANGUAGE_CODE);
    }

    return !is_wp_error($terms) ? $terms : false;
}


/**
 * This function queries database directliy bypassing wp default functions
 * for database queries which is needed for many search and autocomplete
 * functionalities
 *
 * @since Pukka 1.2.1
 *
 * @param $args mixed
 *
 * @return mixed
 */
function pukka_get_posts_by($args){

    /*
    // expected $args fields
    $defaults = array(
        'term' => '',
        'no_post_ids' => '',
        'post_ids' => '',
        'cat' => '',
        'meta' => '',
        'lang' => '',
        'post_type' => 'post',
        'post_status' => 'publish'
    );
    */
    global $wpdb;

    $term = empty($args['term']) ? '' : $args['term'];
    $status = empty($args['post_status']) ? 'publish' : $args['post_status'];

    $posts = $wpdb->posts;
    $terms_rel = $wpdb->term_relationships;
    $terms_tax = $wpdb->term_taxonomy;

    // if specific post type is set, get just that
    if(!empty($args['post_type'])){
        if(is_array($args['post_type'])){
            $post_types = " AND (";
            $cnt = count($args['post_type']);
            for($i = 0; $i < $cnt; $i++){
                $post_types .= "$posts.post_type = '" . $args['post_type'][$i] . "'";
                if($i < $cnt - 1){
                    $post_types .= " OR ";
                }
            }

            $post_types .= ")";
        }else{
            $post_types = " AND $posts.post_type = '{$args['post_type']}'";
        }
    }else{
        // if not, get everithing except attachments, revisions and menu items
        $post_types = " AND $posts.post_type <> 'attachment' AND $posts.post_type <> 'revision' AND $posts.post_type <> 'nav_menu_item'";
    }

    $query = "SELECT $posts.ID, $posts.post_title, $posts.post_author, $posts.post_date, $posts.post_type
        FROM $posts";

    // if we want post from specific category, here we can set which one
    // TODO: add multicat select
    if(!empty($args['cat'])){
        $query .= " INNER JOIN $terms_rel ON $posts.ID = $terms_rel.object_id
                    INNER JOIN $terms_tax ON $terms_rel.term_taxonomy_id = $terms_tax.term_taxonomy_id
                    WHERE $terms_tax.term_id = {$args['cat']} AND";
    }else{
        $query .= " WHERE";
    }
    $query .= " $posts.post_title LIKE '%%%s%%'
                AND $posts.post_status = '$status'";

    //if WPML active, query only posts in current language
    if (!empty($args['lang'])) {
        $query .= " AND $posts.ID IN (SELECT element_id
                                FROM " . $wpdb->prefix . "icl_translations
                                WHERE language_code = '" . $args['lang'] . "')";
    }

    if(!empty($post_types)){
        $query .= $post_types;
    }

    if (!empty($args['no_post_ids'])) {
        // taken from wp-includes/query.php
        // we cant prepare NOT IN statement: (%s) becomes ('1,2,3,4'), so we use array_map instead
        $query .= " AND $posts.ID NOT IN (" . implode(',', array_map('absint', $args['no_post_ids'])) . ")";
    }

    if (!empty($args['post_ids'])) {
        // taken from wp-includes/query.php
        // we cant prepare IN statement: (%s) becomes ('1,2,3,4'), so we use array_map instead
        $query .= " AND $posts.ID IN (" . implode(',', array_map('absint', $args['post_ids'])) . ")";
    }

    $results = $wpdb->get_results($wpdb->prepare($query, $term), OBJECT);
    

    return $results;

}