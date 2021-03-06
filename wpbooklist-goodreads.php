<?php
/**
 * WordPress Book List Goodreads Extension
 *
 * @package     WordPress Book List Goodreads Extension
 * @author      Jake Evans
 * @copyright   2018 Jake Evans
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: WPBookList Goodreads Extension
 * Plugin URI: https://www.jakerevans.com
 * Description: Import your entire Goodreads Library with just a few clicks!
 * Version: 1.0.0
 * Author: Jake Evans
 * Text Domain: wpbooklist
 * Author URI: https://www.jakerevans.com
 */

/*
 * SETUP NOTES:
 *
 * Change all filename instances from goodreads to desired plugin name
 *
 * Modify Plugin Name
 *
 * Modify Description
 *
 * Modify Version Number in Block comment and in Constant
 *
 * Find & Replace these 3 strings:
 * goodreads
 * Goodreads
 * GOODREADS
 *
 *
 * Change the EDD_SL_ITEM_ID_GOODREADS contant below.
 *
 * Install Gulp & all Plugins listed in gulpfile.js
 *
 *
 *
 *
 */




// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

/* REQUIRE STATEMENTS */
	require_once 'includes/class-goodreads-general-functions.php';
	require_once 'includes/class-goodreads-ajax-functions.php';
	require_once 'includes/classes/update/class-wpbooklist-goodreads-update.php';
/* END REQUIRE STATEMENTS */

/* CONSTANT DEFINITIONS */

	if ( ! defined('WPBOOKLIST_VERSION_NUM' ) ) {
		define( 'WPBOOKLIST_VERSION_NUM', '6.1.5' );
	}

	// This is the URL our updater / license checker pings. This should be the URL of the site with EDD installed.
	define( 'EDD_SL_STORE_URL_GOODREADS', 'https://wpbooklist.com' );

	// The id of your product in EDD.
	define( 'EDD_SL_ITEM_ID_GOODREADS', 753 );

	// This Extension's Version Number.
	define( 'WPBOOKLIST_GOODREADS_VERSION_NUM', '1.0.0' );

	// Root plugin folder directory.
	define( 'GOODREADS_ROOT_DIR', plugin_dir_path( __FILE__ ) );

	// Root WordPress Plugin Directory. The If is for taking into account the update process - a temp folder gets created when updating, which temporarily replaces the 'wpbooklist-bulkbookupload' folder.
	if ( false !== stripos( plugin_dir_path( __FILE__ ) , '/wpbooklist-goodreads' ) ) { 
		define( 'GOODREADS_ROOT_WP_PLUGINS_DIR', str_replace( '/wpbooklist-goodreads', '', plugin_dir_path( __FILE__ ) ) );
	} else {
		$temp = explode( 'plugins/', plugin_dir_path( __FILE__ ) );
		define( 'GOODREADS_ROOT_WP_PLUGINS_DIR', $temp[0] . 'plugins/' );
	}

	// Root WPBL Dir.
	if ( ! defined('GOODREADS_ROOT_WPBL_DIR' ) ) {
		define( 'GOODREADS_ROOT_WPBL_DIR', GOODREADS_ROOT_WP_PLUGINS_DIR . 'wpbooklist/' );
	}

		// Root WPBL Dir.
	if ( ! defined( 'ROOT_WPBL_DIR' ) ) {
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

	// Root plugin folder URL .
	define( 'GOODREADS_ROOT_URL', plugins_url() . '/wpbooklist-goodreads/' );

	// Root Classes Directory.
	define( 'GOODREADS_CLASS_DIR', GOODREADS_ROOT_DIR . 'includes/classes/' );

	// Root Update Directory.
	define( 'GOODREADS_UPDATE_DIR', GOODREADS_CLASS_DIR . 'update/' );

	// Root REST Classes Directory.
	define( 'GOODREADS_CLASS_REST_DIR', GOODREADS_ROOT_DIR . 'includes/classes/rest/' );

	// Root Compatability Classes Directory.
	define( 'GOODREADS_CLASS_COMPAT_DIR', GOODREADS_ROOT_DIR . 'includes/classes/compat/' );

	// Root Transients Directory.
	define( 'GOODREADS_CLASS_TRANSIENTS_DIR', GOODREADS_ROOT_DIR . 'includes/classes/transients/' );

	// Root Image URL.
	define( 'GOODREADS_ROOT_IMG_URL', GOODREADS_ROOT_URL . 'assets/img/' );

	// Root Image Icons URL.
	define( 'GOODREADS_ROOT_IMG_ICONS_URL', GOODREADS_ROOT_URL . 'assets/img/icons/' );

	// Root CSS URL.
	define( 'GOODREADS_CSS_URL', GOODREADS_ROOT_URL . 'assets/css/' );

	// Root JS URL.
	define( 'GOODREADS_JS_URL', GOODREADS_ROOT_URL . 'assets/js/' );

	// Root UI directory.
	define( 'GOODREADS_ROOT_INCLUDES_UI', GOODREADS_ROOT_DIR . 'includes/ui/' );

	// Root UI Admin directory.
	define( 'GOODREADS_ROOT_INCLUDES_UI_ADMIN_DIR', GOODREADS_ROOT_DIR . 'includes/ui/admin/' );

	// Define the Uploads base directory.
	$uploads     = wp_upload_dir();
	$upload_path = $uploads['basedir'];
	define( 'GOODREADS_UPLOADS_BASE_DIR', $upload_path . '/' );

	// Define the Uploads base URL.
	$upload_url = $uploads['baseurl'];
	define( 'GOODREADS_UPLOADS_BASE_URL', $upload_url . '/' );

	// Define the Goodreads base directory
	define('GOODREADS_UPLOAD_DIR', GOODREADS_UPLOADS_BASE_DIR.'wpbooklist/goodreads/');

	// Define the Goodreads base directory
	define('GOODREADS_TEMP_UPLOAD_DIR', GOODREADS_UPLOADS_BASE_DIR.'wpbooklist/goodreads/tempdir/');

	// Nonces array.
	define( 'GOODREADS_NONCES_ARRAY',
		wp_json_encode(array(
			'adminnonce1' => 'wpbooklist_goodreads_save_license_key_action_callback',
			'adminnonce2' => 'wpbooklist_goodreads_add_new_action_callback',
			'adminnonce3' => 'wpbooklist_goodreads_restore_actual_action_callback',
		))
	);

/* END OF CONSTANT DEFINITIONS */

/* MISC. INCLUSIONS & DEFINITIONS */

	// Loading textdomain.
	load_plugin_textdomain( 'wpbooklist', false, GOODREADS_ROOT_DIR . 'languages' );

/* END MISC. INCLUSIONS & DEFINITIONS */

/* CLASS INSTANTIATIONS */

	// Call the class found in wpbooklist-functions.php.
	$goodreads_general_functions = new Goodreads_General_Functions();

	// Call the class found in wpbooklist-functions.php.
	$goodreads_ajax_functions = new Goodreads_Ajax_Functions();

	// Include the Update Class.
	$goodreads_update_functions = new WPBookList_Goodreads_Update();


/* END CLASS INSTANTIATIONS */


/* FUNCTIONS FOUND IN CLASS-WPBOOKLIST-GENERAL-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */

	// Function that adds in the License Key Submission form on this Extension's entry on the plugins page.
	add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $goodreads_general_functions, 'wpbooklist_goodreads_pluginspage_nonce_entry' ) );


	// Adding the function that will take our GOODREADS_NONCES_ARRAY Constant from above and create actual nonces to be passed to Javascript functions.
	add_action( 'init', array( $goodreads_general_functions, 'wpbooklist_goodreads_create_nonces' ) );

	// Function to run any code that is needed to modify the plugin between different versions.
	add_action( 'plugins_loaded', array( $goodreads_general_functions, 'wpbooklist_goodreads_update_upgrade_function' ) );

	// Adding the admin js file.
	add_action( 'admin_enqueue_scripts', array( $goodreads_general_functions, 'wpbooklist_goodreads_admin_js' ) );

	// Adding the frontend js file.
	add_action( 'wp_enqueue_scripts', array( $goodreads_general_functions, 'wpbooklist_goodreads_frontend_js' ) );

	// Adding the admin css file for this extension.
	add_action( 'admin_enqueue_scripts', array( $goodreads_general_functions, 'wpbooklist_goodreads_admin_style' ) );

	// Adding the Front-End css file for this extension.
	add_action( 'wp_enqueue_scripts', array( $goodreads_general_functions, 'wpbooklist_goodreads_frontend_style' ) );

	// Function to add table names to the global $wpdb.
	add_action( 'admin_footer', array( $goodreads_general_functions, 'wpbooklist_goodreads_register_table_name' ) );

	// Function taht adds in any possible admin pointers
	add_action( 'admin_footer', array( $goodreads_general_functions, 'wpbooklist_goodreads_admin_pointers_javascript' ) );

	// Creates tables upon activation.
	register_activation_hook( __FILE__, array( $goodreads_general_functions, 'wpbooklist_goodreads_create_tables' ) );

	// Runs once upon extension activation and adds it's version number to the 'extensionversions' column in the 'wpbooklist_jre_user_options' table of the core plugin.
	register_activation_hook( __FILE__, array( $goodreads_general_functions, 'wpbooklist_goodreads_record_extension_version' ) );

	// And in the darkness bind them.
	add_filter( 'admin_footer', array( $goodreads_general_functions, 'wpbooklist_goodreads_smell_rose' ) );

	// Displays the 'Enter Your License Key' message at the top of the dashboard if the user hasn't done so already.
	add_action( 'admin_notices', array( $goodreads_general_functions, 'wpbooklist_goodreads_top_dashboard_license_notification' ) );

	global $wpdb;
	$test_name = $wpdb->prefix . 'wpbooklist_goodreads_settings';
	if ( $test_name === $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {
		$extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_goodreads_settings' );
		if ( false !== stripos( $extension_settings->jfsl, 'aod' ) ) {
			add_filter( 'wpbooklist_add_sub_menu', array( $goodreads_general_functions, 'wpbooklist_goodreads_submenu' ) );
		}
	}


	// Function that adds in the License Key Submission form on this Extension's entry on the plugins page.
	add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $goodreads_general_functions, 'wpbooklist_goodreads_pluginspage_nonce_entry' ) );

	// Verifies that the core WPBookList plugin is installed and activated - otherwise, the Extension doesn't load and a message is displayed to the user.
	register_activation_hook( __FILE__, array( $goodreads_general_functions, 'wpbooklist_goodreads_core_plugin_required' ) );

	// For scheduling actual goodreads db imports.
	add_action( 'goodreads_single_cron_job', array( $goodreads_general_functions, 'goodreads_single_cron_function' ), 1, 6);

/* END OF FUNCTIONS FOUND IN CLASS-WPBOOKLIST-GENERAL-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */

/* FUNCTIONS FOUND IN CLASS-WPBOOKLIST-AJAX-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */

	// For receiving user feedback upon deactivation & deletion.
	add_action( 'wp_ajax_goodreads_exit_results_action', array( $goodreads_ajax_functions, 'goodreads_exit_results_action_callback' ) );

	add_action( 'wp_ajax_wpbooklist_goodreads_save_license_key_action', array( $goodreads_ajax_functions, 'wpbooklist_goodreads_save_license_key_action_callback' ) );

	add_action( 'wp_ajax_wpbooklist_goodreads_add_new_action', array( $goodreads_ajax_functions, 'wpbooklist_goodreads_add_new_action_callback' ) );

	add_action( 'wp_ajax_wpbooklist_goodreads_restore_actual_action', array( $goodreads_ajax_functions, 'wpbooklist_goodreads_restore_actual_action_callback' ) );

/* END OF FUNCTIONS FOUND IN CLASS-WPBOOKLIST-AJAX-FUNCTIONS.PHP THAT APPLY PLUGIN-WIDE */






















