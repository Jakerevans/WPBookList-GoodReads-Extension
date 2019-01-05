<?php
/**
 * Class Goodreads_Ajax_Functions - class-wpbooklist-ajax-functions.php
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes
 * @version  6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Goodreads_Ajax_Functions', false ) ) :
	/**
	 * Goodreads_Ajax_Functions class. Here we'll do things like enqueue scripts/css, set up menus, etc.
	 */
	class Goodreads_Ajax_Functions {

		/**
		 * Class Constructor - Simply calls the Translations
		 */
		public function __construct() {


		}

		/**
		 * Callback function for handling the saving of the user's License Key.
		 */
		public function wpbooklist_goodreads_save_license_key_action_callback() {

			global $wpdb;

			check_ajax_referer( 'wpbooklist_goodreads_save_license_key_action_callback', 'security' );

			if ( isset( $_POST['license'] ) ) {
				$license = filter_var( wp_unslash( $_POST['license'] ), FILTER_SANITIZE_STRING );
			}

			$data         = array(
				'jfsl' => $license,
			);
			$format       = array( '%s' );
			$where        = array( 'ID' => 1 );
			$where_format = array( '%d' );
			$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_goodreads_settings', $data, $where, $format, $where_format );

			wp_die( $save_result );

		}

		/**
		 * Function for adding a new Goodreads file.
		 */
		public function wpbooklist_goodreads_add_new_action_callback(){
			global $wpdb;
			check_ajax_referer( 'wpbooklist_goodreads_add_new_action_callback', 'security' );

			// Proceed if it's a valid .csv goodreads file
			if(strpos($_FILES['my_uploaded_file']['name'], '.csv') !== false && strpos($_FILES['my_uploaded_file']['name'], 'goodreads_library_export') !== false){
				// Create file structure in the uploads dir 
				$mkdir1 = null;
				if (!file_exists(UPLOADS_BASE_DIR."wpbooklist")) {
					// TODO: create log file entry 
					$mkdir1 = mkdir(UPLOADS_BASE_DIR."wpbooklist", 0777, true);
				}

				// Create file structure in the uploads dir 
				$mkdir2 = null;
				if (!file_exists(GOODREADS_UPLOAD_DIR)) {
					// TODO: create log file entry 
					$mkdir2 = mkdir(GOODREADS_UPLOAD_DIR, 0777, true);
				}

				// TODO: create log file entry 
				echo $move_result = move_uploaded_file($_FILES['my_uploaded_file']['tmp_name'], GOODREADS_UPLOAD_DIR."{$_FILES['my_uploaded_file'] ['name']}".'_-_'.date('m-d-y').'_-_'.time());

			} else {
				echo 0;
			}

			wp_die();
		}

		/**
		 * Function for actually importing books.ß
		 */
		public function wpbooklist_goodreads_restore_actual_action_callback(){
			global $wpdb;
			check_ajax_referer( 'wpbooklist_goodreads_restore_actual_action_callback', 'security' );
			$filename = filter_var($_POST['filename'],FILTER_SANITIZE_STRING);
			$library = filter_var($_POST['library'],FILTER_SANITIZE_STRING);
			$howtoupload = filter_var($_POST['howtoupload'],FILTER_SANITIZE_STRING);
			$pageyes = filter_var($_POST['pageyes'],FILTER_SANITIZE_STRING);
			$postyes = filter_var($_POST['postyes'],FILTER_SANITIZE_STRING);
			$woocommerce = filter_var($_POST['woocommerce'],FILTER_SANITIZE_STRING);
			
			if(!file_exists(GOODREADS_TEMP_UPLOAD_DIR)){
				mkdir(GOODREADS_TEMP_UPLOAD_DIR);
			}

			// get all previously-created temp files and delete them
		    $files = glob(GOODREADS_TEMP_UPLOAD_DIR."*"); 
		    foreach($files as $file){
		      if(is_file($file))
		        unlink($file);
		    }

		    // Copy Goodreads backup into the temp dir
		    copy(GOODREADS_UPLOAD_DIR.$filename, GOODREADS_TEMP_UPLOAD_DIR.$filename);

		    // Controls how many entries are in each csv file
		    $splitSize = 10;
		    $in = fopen(GOODREADS_TEMP_UPLOAD_DIR.$filename, 'r');
		    $rowCount = 0;
		    $fileCount = 1;
		    $header_row = fgetcsv($in);

		    // The while loop that splits the uploaded file into multiple files
		    while (!feof($in)) {
		      if (($rowCount % $splitSize) == 0) {
		          if ($rowCount > 0) {
		              fclose($out);
		          }
		          $out = fopen(GOODREADS_TEMP_UPLOAD_DIR.$filename . $fileCount++ . '.csv', 'w');
		          fputcsv($out, $header_row);
		      }
		      $data = fgetcsv($in);
		      if ($data){
		          fputcsv($out, $data);
		      }
		      $rowCount++;
		    }
		    
		    fclose($out);

		    if($howtoupload == 'overwrite'){
		      $delete = $wpdb->query("TRUNCATE TABLE $library");
		    }

		    $control = 0;
			unlink(GOODREADS_TEMP_UPLOAD_DIR.$filename); // delete file
			// For each file in the folder, schedule an event to run instantly
			foreach(glob(GOODREADS_TEMP_UPLOAD_DIR.$filename."*.*") as $filename){
			  set_time_limit(0);
			  $file = substr(strrchr($filename,'/'),1);
			  $args = array($library, $file, $howtoupload, $pageyes, $postyes, $woocommerce);
			  wp_schedule_single_event (time()+$control, 'goodreads_single_cron_job', $args);
			  $control = $control+210;
			}

			// Following code creates a request to a file on my server that makes a cron job to ping the user's wp-cron/php file once every minute to ensure that the goodreads wp-cron jobs get executed.
			$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https://' : 'http://';
			$cron_path = $protocol.$_SERVER['SERVER_NAME'].'/wp-cron.php';
			$url = 'https://www.jakerevans.com/forgoodreadscron.php';
			$data = array('pathtouserscron' => $cron_path);

			// use key 'http' even if you send the request to https://...
			$options = array(
				'http' => array(
				    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				    'method'  => 'POST',
				    'content' => http_build_query($data)
				)
			);
			$context  = stream_context_create($options);
			$result = file_get_contents($url, false, $context);

			// Use cUrl if file_get_contents failed
			if($result === false){
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$url = 'https://www.jakerevans.com/forgoodreadscron.php';
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
				$result = curl_exec($ch);
				$responsecode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
			}

			if($result){
				echo 1;
			}

			wp_die();
		}
	}
endif;
