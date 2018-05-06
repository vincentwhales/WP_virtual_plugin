<?php
/*
Plugin Name: Wordpress Virtual Page Plugin List
Plugin URI: https://github.com/Serget/WP_virtual_plugin
Description: Plugin returns JSON from get_plugins() function
Author: Sergey Fesenko
Version: 1.0
*/
    require_once('updater.php');
	if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
		$config = array(
			'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
			'proper_folder_name' => 'vp-plugin-list', // this is the name of the folder your plugin lives in
			'api_url' => 'https://api.github.com/repos/Serget/WP_virtual_plugin', // the GitHub API url of your GitHub repo
			'raw_url' => 'https://raw.github.com/Serget/WP_virtual_plugin/master', // the GitHub raw url of your GitHub repo
			'github_url' => 'https://github.com/Serget/WP_virtual_plugin', // the GitHub url of your GitHub repo
			'zip_url' => 'https://github.com/Serget/WP_virtual_plugin/zipball/master', // the zip url of the GitHub repo
			'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
			'requires' => '3.0', // which version of WordPress does your plugin require?
			'tested' => '4.9', // which version of WordPress is your plugin tested up to?
			'readme' => 'README.md', // which file to use as the readme for the version number
			'access_token' => '', // Access private repositories by authorizing under Appearance > GitHub Updates when this example plugin is installed
		);
		new WP_GitHub_Updater($config);
	}


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
