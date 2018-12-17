<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*
Plugin Name: WPBookList Goodreads Extension
Plugin URI: https://www.jakerevans.com
Description: Goodreads for WPBookList Extensions that wish to insert a new submenu in one of the WPBookList Submenu pages
Version: 1.1.0
Author: Jake Evans - Forward Creation
Author URI: https://www.jakerevans.com
License: GPL2
*/ 

/*
CHANGELOG
= 1.1.0 =
	1. Added support for creating WooCommerce Products in conjunction with the StoreFront Extension
*/

global $wpdb;
require_once('includes/goodreads-functions.php');
require_once('includes/goodreads-ajaxfunctions.php');

// Root plugin folder directory.
if ( ! defined('WPBOOKLIST_VERSION_NUM' ) ) {
	define( 'WPBOOKLIST_VERSION_NUM', '6.1.2' );
}

// This Extension's Version Number.
define( 'WPBOOKLIST_GOODREADS_VERSION_NUM', '6.1.2' );

// Root plugin folder URL of this extension
define('GOODREADS_ROOT_URL', plugins_url().'/wpbooklist-goodreads/');

// Grabbing db prefix
define('GOODREADS_PREFIX', $wpdb->prefix);

// Define the Uploads base directory
$uploads = wp_upload_dir();
$upload_path = $uploads['basedir'];
define('GOODREADS_UPLOADS_BASE_DIR', $upload_path.'/');

// Root plugin folder directory for this extension
define('GOODREADS_ROOT_DIR', plugin_dir_path(__FILE__));

// Root WordPress Plugin Directory.
define( 'GOODREADS_ROOT_WP_PLUGINS_DIR', str_replace( '/wpbooklist-goodreads', '', plugin_dir_path( __FILE__ ) ) );

// Root WPBL Dir.
if ( ! defined('ROOT_WPBL_DIR' ) ) {
	define( 'ROOT_WPBL_DIR', GOODREADS_ROOT_WP_PLUGINS_DIR . 'wpbooklist/' );
}

// Root WPBL Url.
if ( ! defined('ROOT_WPBL_URL' ) ) {
	define( 'ROOT_WPBL_URL', plugins_url() . '/wpbooklist/' );
}

// Root WPBL Classes Dir.
if ( ! defined('ROOT_WPBL_CLASSES_DIR' ) ) {
	define( 'ROOT_WPBL_CLASSES_DIR', ROOT_WPBL_DIR . 'includes/classes/' );
}

// Root WPBL Transients Dir.
if ( ! defined('ROOT_WPBL_TRANSIENTS_DIR' ) ) {
	define( 'ROOT_WPBL_TRANSIENTS_DIR', ROOT_WPBL_CLASSES_DIR . 'transients/' );
}

// Root WPBL Translations Dir.
if ( ! defined('ROOT_WPBL_TRANSLATIONS_DIR' ) ) {
	define( 'ROOT_WPBL_TRANSLATIONS_DIR', ROOT_WPBL_CLASSES_DIR . 'translations/' );
}

// Root WPBL Root Img Icons Dir.
if ( ! defined('ROOT_WPBL_IMG_ICONS_URL' ) ) {
	define( 'ROOT_WPBL_IMG_ICONS_URL', ROOT_WPBL_URL . 'assets/img/icons/' );
}

// Root WPBL Root Utilities Dir.
if ( ! defined('ROOT_WPBL_UTILITIES_DIR' ) ) {
	define( 'ROOT_WPBL_UTILITIES_DIR', ROOT_WPBL_CLASSES_DIR . 'utilities/' );
}

// Root WPBL Dir.
if ( ! defined('ROOT_WPBL_DIR' ) ) {
	define( 'ROOT_WPBL_DIR', COMMENTS_ROOT_WP_PLUGINS_DIR . 'wpbooklist/' );
}

// Root Image Icons URL of this extension
define('GOODREADS_ROOT_IMG_ICONS_URL', GOODREADS_ROOT_URL.'assets/img/');

// Root Classes Directory for this extension
define('GOODREADS_CLASS_DIR', GOODREADS_ROOT_DIR.'includes/classes/');

// Define the Goodreads base directory
define('GOODREADS_UPLOAD_DIR', GOODREADS_UPLOADS_BASE_DIR.'wpbooklist/goodreads/');

// Define the Goodreads base directory
define('GOODREADS_TEMP_UPLOAD_DIR', GOODREADS_UPLOADS_BASE_DIR.'wpbooklist/goodreads/tempdir/');

// Root CSS URL for this extension
define('GOODREADS_ROOT_CSS_URL', GOODREADS_ROOT_URL.'assets/css/');

// Adding the front-end ui css file for this extension
add_action('wp_enqueue_scripts', 'wpbooklist_jre_goodreads_frontend_ui_style');

// Adding the admin css file for this extension
add_action('admin_enqueue_scripts', 'wpbooklist_jre_goodreads_admin_style' );

// Function to upload a new Goodreads Export
add_action( 'admin_footer', 'wpbooklist_goodreads_add_new_action_javascript' );
add_action( 'wp_ajax_wpbooklist_goodreads_add_new_action', 'wpbooklist_goodreads_add_new_action_callback' );
add_action( 'wp_ajax_nopriv_wpbooklist_goodreads_add_new_action', 'wpbooklist_goodreads_add_new_action_callback' );

// For actually adding books from a Goodreads export
add_action( 'admin_footer', 'wpbooklist_goodreads_restore_actual_action_javascript' );
add_action( 'wp_ajax_wpbooklist_goodreads_restore_actual_action', 'wpbooklist_goodreads_restore_actual_action_callback' );
add_action( 'wp_ajax_nopriv_wpbooklist_goodreads_restore_actual_action', 'wpbooklist_goodreads_restore_actual_action_callback' );

// Verifies that the core WPBookList plugin is installed and activated - otherwise, the Extension doesn't load and a message is displayed to the user.
register_activation_hook( __FILE__, 'wpbooklist_goodreads_core_plugin_required' );


// For scheduling actual goodreads db imports
add_action('goodreads_single_cron_job','goodreads_single_cron_function', 1, 6);
/*
 * Function that utilizes the filter in the core WPBookList plugin, resulting in a new submenu. Possible options for the first argument in the 'Add_filter' function below are:
 *  - 'wpbooklist_add_submenu_books'
 *  - 'wpbooklist_add_submenu_display'
 *
 *
 *
 * The instance of "Goodreads" in the $extra_submenu array can be replaced with whatever you want - but the 'goodreads' instance MUST be your one-word descriptor.
*/
add_filter('wpbooklist_add_sub_menu', 'wpbooklist_goodreads_submenu');
function wpbooklist_goodreads_submenu($submenu_array) {
 	$extra_submenu = array(
		'Goodreads'
	);
 
	// combine the two arrays
	$submenu_array = array_merge($submenu_array,$extra_submenu);
	return $submenu_array;
}

?>