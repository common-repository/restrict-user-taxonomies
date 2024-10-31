<?php
/*
Plugin Name: Restrict User Taxonomies
Description: Based on Restrict Categories, this plugin allows you to restrict post adding, viewing and editing based on selected taxonomy terms.
Version: 1.1.0
Author: Sibin Grasic
Author URI: https://sgi.io
Text Domain: restrict-user-taxonomies
*/

/* Prevent Direct access */
if ( !defined( 'DB_NAME' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	die;
}

/*Define plugin main file*/
if ( !defined('SGI_RAT_FILE') )
	define ( 'SGI_RAT_FILE', __FILE__ );

/* Define BaseName */
if ( !defined('SGI_RAT_BASENAME') )
	define ('SGI_RAT_BASENAME',plugin_basename(SGI_RAT_FILE));

/* Define internal path */
if ( !defined( 'SGI_RAT_PATH' ) )
	define( 'SGI_RAT_PATH', plugin_dir_path( SGI_RAT_FILE ) );

/* Define internal version for possible update changes */
define ('SGI_RAT_VERSION', '1.1.0');

/* Load Up the text domain */
function sgi_rat_load_textdomain()
{

	load_plugin_textdomain('restrict-user-taxonomies', false, basename( dirname( __FILE__ ) ) . '/languages' );

}

add_action('wp_loaded','sgi_rat_load_textdomain');

/* Check if we're running compatible software */
if ( version_compare( PHP_VERSION, '5.3', '<' ) && version_compare(WP_VERSION, '3.8', '<') ) :
	if (is_admin()) :
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( __FILE__ );
		wp_die(__('Restrict User taxonomies plugin requires WordPress 3.8 and PHP 5.3 or greater. The plugin has now disabled itself','restrict-user-taxonomies'));
	endif;
endif;

/* Let's load up our plugin */

require_once (SGI_RAT_PATH.'lib/utils.php');

function sgi_rat_backend_init()
{
	require_once (SGI_RAT_PATH.'lib/backend/rat-admin-main.php');
	require_once (SGI_RAT_PATH.'lib/backend/rat-admin-settings.php');
	require_once (SGI_RAT_PATH.'lib/backend/rat-admin-user.php');

	new SGI_RAT_Admin();
	new SGI_RAT_Settings();
	new SGI_RAT_User();
}

function sgi_rat_ajax_init()
{

	require_once (SGI_RAT_PATH.'lib/ajax/rat-get-terms.php');
	new SGI_RAT_Get_Terms();

}

if (is_admin()) : 

	add_action('init', 'sgi_rat_backend_init', 500);

endif;

if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) :

	add_action('init', 'sgi_rat_ajax_init');

endif;