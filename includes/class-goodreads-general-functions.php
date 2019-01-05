<?php
/**
 * Class Goodreads_General_Functions - class-goodreads-general-functions.php
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes
 * @version  6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Goodreads_General_Functions', false ) ) :
	/**
	 * Goodreads_General_Functions class. Here we'll do things like enqueue scripts/css, set up menus, etc.
	 */
	class Goodreads_General_Functions {

		/**
		 * Verifies that the core WPBookList plugin is installed and activated - otherwise, the Extension doesn't load and a message is displayed to the user.
		 */
		public function wpbooklist_goodreads_core_plugin_required() {

			// Require core WPBookList Plugin.
			if ( ! is_plugin_active( 'wpbooklist/wpbooklist.php' ) && current_user_can( 'activate_plugins' ) ) {

				// Stop activation redirect and show error.
				wp_die( 'Whoops! This WPBookList Extension requires the Core WPBookList Plugin to be installed and activated! <br><a target="_blank" href="https://wordpress.org/plugins/wpbooklist/">Download WPBookList Here!</a><br><br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>' );
			}
		}

		/**
		 * Verifies the crown of the rose.
		 *
		 * @param  array $plugins List of plugins to activate & load.
		 */
		public function wpbooklist_goodreads_smell_rose() {

			global $wpdb;

			// Get license key from plugin options, if it's already been saved. If it has, don't display anything.
			$this->extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_goodreads_settings' );

			// If the License Key just hasn't been entered yet...
			if ( null === $this->extension_settings->jfsl || '' === $this->extension_settings->jfsl ) {
				return;
			} else {

				if ( false !== stripos( $this->extension_settings->jfsl, '---' ) ) {

					$temp = explode( '---', $this->extension_settings->jfsl );

					if ( 'aod' === $temp[1] ) {

						// Get the date.
						require_once ROOT_WPBL_UTILITIES_DIR . 'class-wpbooklist-utilities-date.php';
						$utilities_date = new WPBookList_Utilities_Date();
						$this->date     = $utilities_date->wpbooklist_get_date_via_current_time( 'timestamp' );

						if ( 604800 < ( $this->date - (int) $temp[2] ) ) {

							$checker_good_flag = false;

							$san_check = wp_remote_get( 'https://wpbooklist.com/?edd_action=activate_license&item_id=' . EDD_SL_ITEM_ID_GOODREADS . '&license=' . $temp[0] . '&url=' . get_site_url() );

							// Check the response code.
							$response_code    = wp_remote_retrieve_response_code( $san_check );
							$response_message = wp_remote_retrieve_response_message( $san_check );

							if ( 200 !== $response_code && ! empty( $response_message ) ) {
								return new WP_Error( $response_code, $response_message );
							} elseif ( 200 !== $response_code ) {
								$this->apireport = $this->apireport . 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function ';
								return new WP_Error( $response_code, 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function' );
							} else {
								$san_check = wp_remote_retrieve_body( $san_check );
								$san_check = json_decode( $san_check, true );

								if ( 'valid' === $san_check['license'] && $san_check['success'] ) {

									$this->date = $utilities_date->wpbooklist_get_date_via_current_time( 'timestamp' );

									$data         = array(
										'jfsl' => $temp[0] . '---aod---' . $this->date,
									);
									$format       = array( '%s' );
									$where        = array( 'ID' => 1 );
									$where_format = array( '%d' );
									$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_goodreads_settings', $data, $where, $format, $where_format );

									$checker_good_flag = true;
								} else {
									$data         = array(
										'jfsl' => '',
									);
									$format       = array( '%s' );
									$where        = array( 'ID' => 1 );
									$where_format = array( '%d' );
									$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_goodreads_settings', $data, $where, $format, $where_format );
								}
							}

							if ( ! $checker_good_flag ) {
								deactivate_plugins( GOODREADS_ROOT_DIR . 'wpbooklist-goodreads.php' );
								return;
							}
						} else {
							return;
						}
					} else {

						$checker_good_flag = false;

						$san_check = wp_remote_get( 'https://wpbooklist.com/?edd_action=activate_license&item_id=' . EDD_SL_ITEM_ID_GOODREADS . '&license=' . $this->extension_settings->jfsl . '&url=' . get_site_url() );

						// Check the response code.
						$response_code    = wp_remote_retrieve_response_code( $san_check );
						$response_message = wp_remote_retrieve_response_message( $san_check );

						if ( 200 !== $response_code && ! empty( $response_message ) ) {
							return new WP_Error( $response_code, $response_message );
						} elseif ( 200 !== $response_code ) {
							$this->apireport = $this->apireport . 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function ';
							return new WP_Error( $response_code, 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function' );
						} else {
							$san_check = wp_remote_retrieve_body( $san_check );
							$san_check = json_decode( $san_check, true );

							if ( 'valid' === $san_check['license'] && $san_check['success'] ) {

								// Get the date.
								require_once ROOT_WPBL_UTILITIES_DIR . 'class-wpbooklist-utilities-date.php';
								$utilities_date = new WPBookList_Utilities_Date();
								$this->date     = $utilities_date->wpbooklist_get_date_via_current_time( 'timestamp' );

								$data         = array(
									'jfsl' => $this->extension_settings->jfsl . '---aod---' . $this->date,
								);
								$format       = array( '%s' );
								$where        = array( 'ID' => 1 );
								$where_format = array( '%d' );
								$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_goodreads_settings', $data, $where, $format, $where_format );

								$checker_good_flag = true;

							} else {
								$data         = array(
									'jfsl' => '',
								);
								$format       = array( '%s' );
								$where        = array( 'ID' => 1 );
								$where_format = array( '%d' );
								$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_goodreads_settings', $data, $where, $format, $where_format );
							}
						}

						if ( ! $checker_good_flag ) {
							deactivate_plugins( GOODREADS_ROOT_DIR . 'wpbooklist-goodreads.php' );
							return;
						}
					}
				} else {

					$checker_good_flag = false;

					$san_check = wp_remote_get( 'https://wpbooklist.com/?edd_action=activate_license&item_id=' . EDD_SL_ITEM_ID_GOODREADS . '&license=' . $this->extension_settings->jfsl . '&url=' . get_site_url() );

					// Check the response code.
					$response_code    = wp_remote_retrieve_response_code( $san_check );
					$response_message = wp_remote_retrieve_response_message( $san_check );

					if ( 200 !== $response_code && ! empty( $response_message ) ) {
						return new WP_Error( $response_code, $response_message );
					} elseif ( 200 !== $response_code ) {
						$this->apireport = $this->apireport . 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function ';
						return new WP_Error( $response_code, 'Unknown error occurred with wp_remote_get() trying to build Books-a-Million link in the create_buy_links() function' );
					} else {
						$san_check = wp_remote_retrieve_body( $san_check );
						$san_check = json_decode( $san_check, true );

						if ( 'valid' === $san_check['license'] && $san_check['success'] ) {

							// Get the date.
							require_once ROOT_WPBL_UTILITIES_DIR . 'class-wpbooklist-utilities-date.php';
							$utilities_date = new WPBookList_Utilities_Date();
							$this->date     = $utilities_date->wpbooklist_get_date_via_current_time( 'timestamp' );

							$data         = array(
								'jfsl' => $this->extension_settings->jfsl . '---aod---' . $this->date,
							);
							$format       = array( '%s' );
							$where        = array( 'ID' => 1 );
							$where_format = array( '%d' );
							$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_goodreads_settings', $data, $where, $format, $where_format );

							$checker_good_flag = true;

						} else {
							$data         = array(
								'jfsl' => '',
							);
							$format       = array( '%s' );
							$where        = array( 'ID' => 1 );
							$where_format = array( '%d' );
							$save_result = $wpdb->update( $wpdb->prefix . 'wpbooklist_goodreads_settings', $data, $where, $format, $where_format );
						}
					}

					if ( ! $checker_good_flag ) {
						deactivate_plugins( GOODREADS_ROOT_DIR . 'wpbooklist-goodreads.php' );

						if ( isset( $_SERVER['REQUEST_URI'] ) ) {
							//header( 'Location: ' . filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_STRING ) );
						}

						return;
					}
				}
			}
		}

		/**
		 * Adds in the 'Enter License Key' text input and submit button.
		 *
		 * @param  array $links List of existing plugin action links.
		 * @return array List of modified plugin action links.
		 */
		public function wpbooklist_goodreads_pluginspage_nonce_entry( $links ) {

			global $wpdb;

			require_once ROOT_WPBL_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
			$trans = new WPBookList_Translations();

			$this->extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_goodreads_settings' );

			if ( null === $this->extension_settings->jfsl || '' === $this->extension_settings->jfsl ) {
				$value = $trans->trans_613;
			} else {

				if ( false !== stripos( $this->extension_settings->jfsl, '---' ) ) {
					$temp  = explode( '---', $this->extension_settings->jfsl );
					$value = $temp[0];
				} else {
					$value = $this->extension_settings->jfsl;
				}
			}

			$form_html = '
				<form>
					<input id="wpbooklist-extension-genreric-key-plugins-page-input-goodreads" class="wpbooklist-extension-genreric-key-plugins-page-input" type="text" placeholder="' . $trans->trans_613 . '" value="' . $value . '"></input>
					<button id="wpbooklist-extension-genreric-key-plugins-page-button-goodreads" class="wpbooklist-extension-genreric-key-plugins-page-button">' . $trans->trans_614 . '</button>
				</form>';

			array_push( $links, $form_html );

			return $links;

		}

		/**
		 * Displays the 'Enter Your License Key' message at the top of the dashboard if the user hasn't done so already.
		 */
		public function wpbooklist_goodreads_top_dashboard_license_notification() {

			global $wpdb;

			// Get license key from plugin options, if it's already been saved. If it has, don't display anything.
			$this->extension_settings = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'wpbooklist_goodreads_settings' );

			if ( null === $this->extension_settings->jfsl || '' === $this->extension_settings->jfsl ) {

				require_once ROOT_WPBL_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
				$trans = new WPBookList_Translations();

				echo '
				<div class="notice notice-success is-dismissible">
					<form class="wpbooklist-extension-genreric-key-dashboard-form" id="wpbooklist-extension-genreric-key-dashboard-form-goodreads">
						<p class="wpbooklist-extension-genreric-key-dashboard-title">' . $trans->trans_624 . '</p>
						<input id="wpbooklist-extension-genreric-key-dashboard-input-goodreads" class="wpbooklist-extension-genreric-key-dashboard-input" type="text" placeholder="' . $trans->trans_613 . '" value="' . $trans->trans_613 . '"></input>
						<button data-ext="goodreads" id="wpbooklist-extension-genreric-key-dashboard-button-goodreads" class="wpbooklist-extension-genreric-key-dashboard-button">' . $trans->trans_616 . '</button>
					</form>
				</div>';
			}
		}

		/** Functions that loads up the menu page entry for this Extension.
		 *
		 *  @param array $submenu_array - The array that contains submenu entries to add to.
		 */
		public function wpbooklist_goodreads_submenu( $submenu_array ) {
			$extra_submenu = array(
				'Goodreads',
			);

			// Combine the two arrays.
			$submenu_array = array_merge( $submenu_array, $extra_submenu );
			return $submenu_array;
		}

		/**
		 *  Here we take the Constant defined in wpbooklist.php that holds the values that all our nonces will be created from, we create the actual nonces using wp_create_nonce, and the we define our new, final nonces Constant, called WPBOOKLIST_FINAL_NONCES_ARRAY.
		 */
		public function wpbooklist_goodreads_create_nonces() {

			$temp_array = array();
			foreach ( json_decode( GOODREADS_NONCES_ARRAY ) as $key => $noncetext ) {
				$nonce              = wp_create_nonce( $noncetext );
				$temp_array[ $key ] = $nonce;
			}

			// Defining our final nonce array.
			define( 'GOODREADS_FINAL_NONCES_ARRAY', wp_json_encode( $temp_array ) );

		}

		/**
		 *  Runs once upon extension activation and adds it's version number to the 'extensionversions' column in the 'wpbooklist_jre_user_options' table of the core plugin.
		 */
		public function wpbooklist_goodreads_record_extension_version() {
			global $wpdb;
			$existing_string = $wpdb->get_row( 'SELECT * from ' . $wpdb->prefix . 'wpbooklist_jre_user_options' );

			// Check to see if Extension is already registered.
			if ( false !== strpos( $existing_string->extensionversions, 'goodreads' ) ) {
				$split_string = explode( 'goodreads', $existing_string->extensionversions );
				$first_part   = $split_string[0];
				$last_part    = substr( $split_string[1], 5 );
				$new_string   = $first_part . 'goodreads' . WPBOOKLIST_GOODREADS_VERSION_NUM . $last_part;
			} else {
				$new_string = $existing_string->extensionversions . 'goodreads' . WPBOOKLIST_GOODREADS_VERSION_NUM;
			}

			$data         = array(
				'extensionversions' => $new_string,
			);
			$format       = array( '%s' );
			$where        = array( 'ID' => 1 );
			$where_format = array( '%d' );
			$wpdb->update( $wpdb->prefix . 'wpbooklist_jre_user_options', $data, $where, $format, $where_format );

		}

		/**
		 *  Function to run the compatability code in the Compat class for upgrades/updates, if stored version number doesn't match the defined global in wpbooklist-goodreads.php
		 */
		public function wpbooklist_goodreads_update_upgrade_function() {

			// Get current version #.
			global $wpdb;
			$existing_string = $wpdb->get_row( 'SELECT * from ' . $wpdb->prefix . 'wpbooklist_jre_user_options' );

			// Check to see if Extension is already registered and matches this version.
			if ( false !== strpos( $existing_string->extensionversions, 'goodreads' ) ) {
				$split_string = explode( 'goodreads', $existing_string->extensionversions );
				$version      = substr( $split_string[1], 0, 5 );

				// If version number does not match the current version number found in wpbooklist.php, call the Compat class and run upgrade functions.
				if ( WPBOOKLIST_GOODREADS_VERSION_NUM !== $version ) {
					require_once GOODREADS_CLASS_COMPAT_DIR . 'class-goodreads-compat-functions.php';
					$compat_class = new Goodreads_Compat_Functions();
				}
			}
		}

		/**
		 * Adding the admin js file
		 */
		public function wpbooklist_goodreads_admin_js() {

			wp_register_script( 'wpbooklist_goodreads_adminjs', GOODREADS_JS_URL . 'wpbooklist_goodreads_admin.min.js', array( 'jquery' ), WPBOOKLIST_VERSION_NUM, true );

			// Next 4-5 lines are required to allow translations of strings that would otherwise live in the wpbooklist-admin-js.js JavaScript File.
			require_once ROOT_WPBL_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
			$trans = new WPBookList_Translations();

			// Localize the script with the appropriate translation array from the Translations class.
			$translation_array1 = $trans->trans_strings();

			// Now grab all of our Nonces to pass to the JavaScript for the Ajax functions and merge with the Translations array.
			$final_array_of_php_values = array_merge( $translation_array1, json_decode( GOODREADS_FINAL_NONCES_ARRAY, true ) );

			// Adding some other individual values we may need.
			$final_array_of_php_values['GOODREADS_ROOT_IMG_ICONS_URL'] = GOODREADS_ROOT_IMG_ICONS_URL;
			$final_array_of_php_values['GOODREADS_ROOT_IMG_URL']       = GOODREADS_ROOT_IMG_URL;
			$final_array_of_php_values['FOR_TAB_HIGHLIGHT']                         = admin_url() . 'admin.php';
			$final_array_of_php_values['SAVED_ATTACHEMENT_ID']                      = get_option( 'media_selector_attachment_id', 0 );

			// Now registering/localizing our JavaScript file, passing all the PHP variables we'll need in our $final_array_of_php_values array, to be accessed from 'wphealthtracker_php_variables' object (like wphealthtracker_php_variables.nameofkey, like any other JavaScript object).
			wp_localize_script( 'wpbooklist_goodreads_adminjs', 'wpbooklistGoodreadsPhpVariables', $final_array_of_php_values );

			wp_enqueue_script( 'wpbooklist_goodreads_adminjs' );

			return $final_array_of_php_values;

		}

		/**
		 * Adding the frontend js file
		 */
		public function wpbooklist_goodreads_frontend_js() {

			wp_register_script( 'wpbooklist_goodreads_frontendjs', GOODREADS_JS_URL . 'wpbooklist_goodreads_frontend.min.js', array( 'jquery' ), WPBOOKLIST_GOODREADS_VERSION_NUM, true );

			// Next 4-5 lines are required to allow translations of strings that would otherwise live in the wpbooklist-admin-js.js JavaScript File.
			require_once ROOT_WPBL_TRANSLATIONS_DIR . 'class-wpbooklist-translations.php';
			$trans = new WPBookList_Translations();

			// Localize the script with the appropriate translation array from the Translations class.
			$translation_array1 = $trans->trans_strings();

			// Now grab all of our Nonces to pass to the JavaScript for the Ajax functions and merge with the Translations array.
			$final_array_of_php_values = array_merge( $translation_array1, json_decode( GOODREADS_FINAL_NONCES_ARRAY, true ) );

			// Adding some other individual values we may need.
			$final_array_of_php_values['GOODREADS_ROOT_IMG_ICONS_URL'] = GOODREADS_ROOT_IMG_ICONS_URL;
			$final_array_of_php_values['GOODREADS_ROOT_IMG_URL']       = GOODREADS_ROOT_IMG_URL;

			// Now registering/localizing our JavaScript file, passing all the PHP variables we'll need in our $final_array_of_php_values array, to be accessed from 'wphealthtracker_php_variables' object (like wphealthtracker_php_variables.nameofkey, like any other JavaScript object).
			wp_localize_script( 'wpbooklist_goodreads_frontendjs', 'wpbooklistGoodreadsPhpVariables', $final_array_of_php_values );

			wp_enqueue_script( 'wpbooklist_goodreads_frontendjs' );

			return $final_array_of_php_values;

		}

		/**
		 * Adding the admin css file
		 */
		public function wpbooklist_goodreads_admin_style() {

			wp_register_style( 'wpbooklist_goodreads_adminui', GOODREADS_CSS_URL . 'wpbooklist-goodreads-main-admin.css', null, 5 );
			wp_enqueue_style( 'wpbooklist_goodreads_adminui' );

		}

		/**
		 * Adding the frontend css file
		 */
		public function wpbooklist_goodreads_frontend_style() {

			wp_register_style( 'wpbooklist_goodreads_frontendui', GOODREADS_CSS_URL . 'wpbooklist-goodreads-main-frontend.css', null, WPBOOKLIST_GOODREADS_VERSION_NUM );
			wp_enqueue_style( 'wpbooklist_goodreads_frontendui' );

		}

		/**
		 *  Function to add table names to the global $wpdb.
		 */
		public function wpbooklist_goodreads_register_table_name() {
			global $wpdb;
			$wpdb->wpbooklist_goodreads_settings = "{$wpdb->prefix}wpbooklist_goodreads_settings";
		}

		/**
		 *  Function that calls the Style and Scripts needed for displaying of admin pointer messages.
		 */
		public function wpbooklist_goodreads_admin_pointers_javascript() {
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'utils' );
		}

		/**
		 *  Runs once upon plugin activation and creates the table that holds info on WPBookList Pages & Posts.
		 */
		public function wpbooklist_goodreads_create_tables() {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			global $wpdb;
			global $charset_collate;

			// Call this manually as we may have missed the init hook.
			$this->wpbooklist_goodreads_register_table_name();

			$sql_create_table1 = "CREATE TABLE {$wpdb->wpbooklist_goodreads_settings}
			(
				ID bigint(190) auto_increment,
				jfsl varchar(255),
				PRIMARY KEY  (ID),
				KEY jfsl (jfsl)
			) $charset_collate; ";

			// If table doesn't exist, create table and add initial data to it.
			$test_name = $wpdb->prefix . 'wpbooklist_goodreads_settings';
			if ( $test_name !== $wpdb->get_var( "SHOW TABLES LIKE '$test_name'" ) ) {
				dbDelta( $sql_create_table1 );
				$table_name = $wpdb->prefix . 'wpbooklist_goodreads_settings';
				$wpdb->insert( $table_name, array( 'ID' => 1, ) );
			}
		}

		/**
		 *  Function to schedule cron jobs to keep books being added from goodreads.
		 */
		public function goodreads_single_cron_function( $table_name_from, $goodreadsSelection, $append, $page_yes, $post_yes, $woocommerce ) {

			if ( true === $page_yes || 'true' === $page_yes ) {
				$page_yes = 'Yes';
			}

			if ( true === $post_yes || 'true' === $post_yes ) {
				$post_yes = 'Yes';
			}

			if ( true === $woocommerce || 'true' === $woocommerce ) {
				$woocommerce = 'Yes';
			}


			ini_set('memory_limit', '-1');
			ini_set('max_execution_time', 0);
			set_time_limit(0);
			global $wpdb;
			$table_name = $table_name_from;

			$file_url = GOODREADS_TEMP_UPLOAD_DIR.$goodreadsSelection;
			$rows = array_map('str_getcsv', file($file_url));
			$header = array_shift($rows);
			$csv = array();
			foreach ($rows as $row) {
				$csv[] = array_combine($header, $row);
			}

			//for each title that was returned...
			foreach($csv as $indiv_book){
			   
			    // Grabbing the data from the uploaded spreadsheet and setting some default variables
			    if(strlen($indiv_book['ISBN13']) == 16){
			        $isbn = substr($indiv_book['ISBN13'],2,13);
			    } else if(strlen($indiv_book['ISBN10'] > 3)){
			        $isbn = substr($indiv_book['ISBN10'],2,10);
			    } else {
			        $isbn = 'noisbn';
			    }

			    $title = $indiv_book['Title'];
			    $publisher = $indiv_book['Publisher'];
			    $pub_year = $indiv_book['Year Published'];
			    $author = $indiv_book['Author'];
			    $notes = $indiv_book['My Review'];
			    $pages = $indiv_book['Number of Pages'];
			    $date_finished = substr($indiv_book['Date Read'],0,4);
			    $length = strlen($year_finished);

			    // Determine if book has been read
			    if($length == 4){
		        	$finished = 'true';
			    } else {
			        $finished = 'false';
			    }

			    $signed = "false";
		    	$first_edition = "false";

				$book_array = array(
					'library' => $table_name_from,
					'use_amazon_yes' => 'true',
					'amazon_auth_yes' => 'true',
					'isbn' => $isbn,
					'title' => $title,
					'author' => $author,
					'author_url' => $author_url,
					'category' => $category,
					'price' => $price,
					'pages' => $pages,
					'pub_year' => $pub_year,
					'publisher' => $publisher,
					'description' => $description,
					'notes' => $notes,
					'rating' => $rating,
					'image' => $image,
					'finished' => $finished,
					'date_finished' => $date_finished,
					'signed' => $signed,
					'first_edition' => $first_edition,
					'page_yes' => $page_yes,
					'post_yes' => $post_yes,
					'woocommerce' => $woocommerce
				);

				require_once(CLASS_BOOK_DIR.'class-wpbooklist-book.php');
				$book_class = new WPBookList_Book('add', $book_array, null);
				$insert_result = $book_class->add_result;
				error_log($insert_result);
				error_log('ISBN'.$isbn);


			}
		}
























	}
endif;
