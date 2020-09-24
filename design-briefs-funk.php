<?php
/*
Plugin Name: DesignBriefs Funk
Plugin URI: 
Description: Functionality for Designbriefs website
Version: 1.1
Author: Manuel Schmalstieg
Author URI:
License: GPL2

*/


// Header Cleanup

remove_action('wp_head', 'wp_generator');


// Change Archive Title, rename "Tags:" into "Keywords"
// utiliser filtre: get_the_archive_title

add_filter( 'get_the_archive_title', function ( $title ) {
  if( is_tag() ) {
    $title = single_tag_title( 'keyword: ', false );
  }
    return $title;
});


/*

 * Make Posts hierarchical
 Source: https://stackoverflow.com/questions/10750931/wordpress-how-to-add-hierarchy-to-posts
*/

add_action('registered_post_type', 'designbriefs_make_posts_hierarchical', 10, 2 );

// Runs after each post type is registered
function designbriefs_make_posts_hierarchical($post_type, $pto){

    // Return, if not post type posts
    if ($post_type != 'post') return;

    // access $wp_post_types global variable
    global $wp_post_types;

    // Set post type "post" to be hierarchical
    $wp_post_types['post']->hierarchical = 1;

    // Add page attributes to post backend
    // This adds the box to set up parent and menu order on edit posts.
    add_post_type_support( 'post', 'page-attributes' );

}





// Change loop for Front Page and Archive pages: query only for Parent=0 (so we see no child-pages).
// See https://stackoverflow.com/questions/5414669/wordpress-wp-query-query-parent-pages-only

function designbriefs_no_parents( $query ) {
 
        if ( $query->is_archive() && !is_admin() ) {
           	$query->set( 'post_parent', 0);
            return $query;
            
        } else if ( $query->is_home() && $query->is_main_query() ) {
        
        	$query->set( 'post_parent', 0);
        	return $query;
					
        }
}
add_filter( 'pre_get_posts', 'designbriefs_no_parents' );





/*
 * Formidable: Populate Field with Posts
 *
*/

add_filter('frm_setup_new_fields_vars', 'frm_populate_posts', 20, 2);
add_filter('frm_setup_edit_fields_vars', 'frm_populate_posts', 20, 2); //use this function on edit too
function frm_populate_posts($values, $field){
  if($field->id == 86){ //replace 125 with the ID of the field to populate
    $posts = get_posts( array(
    	'post_type' => 'post', 
    	'post_status' => array('publish', 'private'),
    	'post_parent' => 0,
    	'numberposts' => 999, 
    	'orderby' => 'title', 
    	'order' => 'ASC'));
    unset($values['options']);
    $values['options'] = array(''); //remove this line if you are using a checkbox or radio button field
    foreach($posts as $p){
      $values['options'][$p->ID] = $p->post_title;
    }
    $values['use_key'] = true; //this will set the field to save the post ID instead of post title
  }
  return $values;
}

/*
 * Formidable: Load Styles
 *
*/

function designbriefs_plugin_overrides() {
	
	wp_enqueue_style( 'formidable-override', plugin_dir_url( __FILE__ ).'styles/formidable.css' );
	
	wp_enqueue_style( 'featherlight-override', plugin_dir_url( __FILE__ ).'styles/featherlight.css' );
        
}
add_action( 'wp_enqueue_scripts', 'designbriefs_plugin_overrides', 23 );