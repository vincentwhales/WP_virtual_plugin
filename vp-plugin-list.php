<?php
/*
Plugin Name: Wordpress Virtual Page Plugin List
Description: Plugin returns JSON from get_plugins() function
Author: Sergey Fesenko
Version: 1.0
*/


/*
Adding rewrite rules for wordpress_plugins after init and flushing rewrite rules.
*/
function wvppl_activate() {
	add_rewrite_tag( '%wordpress_plugins%', '([^&]+)' );
	add_rewrite_rule( '^wordpress_plugins/?', 'index.php?wordpress_plugins=1','top' );
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wvppl_activate' );


/*
Registring query_var
*/
function wvppl_register_query_vars( $vars ) {
	$vars[] = 'wordpress_plugins';
	return $vars;
}
add_filter( 'query_vars', 'wvppl_register_query_vars' );


/*
Adding rewrite rules for wordpress_plugins
*/
function wvppl_rewrite_tag_rule() {
	add_rewrite_tag( '%wordpress_plugins%', '([^&]+)' );
	add_rewrite_rule( '^wordpress_plugins/?', 'index.php?wordpress_plugins=1','top' );
	
}
add_action('init', 'wvppl_rewrite_tag_rule', 10, 0);



/*
Function to show data about plugins depending on query_var
*/
function wvppl_pre_get_posts( $query ) {
	// check if the user is requesting an admin page 
	// or current query is not the main query
	if ( is_admin() || ! $query->is_main_query() ){
		return;
	}
    
	$wordpress_plugins = get_query_var( 'wordpress_plugins' );
    
	// add meta_query elements
	if( !empty( $wordpress_plugins ) && $wordpress_plugins==1 ){
		require_once $_SERVER['DOCUMENT_ROOT'].'/wp-load.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$all_plugins = get_plugins();
		echo json_encode($all_plugins, JSON_UNESCAPED_SLASHES);
		die();
	}
}
add_action( 'pre_get_posts', 'wvppl_pre_get_posts', 1 );
