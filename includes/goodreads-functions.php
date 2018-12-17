<?php

/**
 * Verifies that the core WPBookList plugin is installed and activated - otherwise, the Extension doesn't load and a message is displayed to the user.
 */
function wpbooklist_goodreads_core_plugin_required() {

	// Require core WPBookList Plugin.
	if ( ! is_plugin_active( 'wpbooklist/wpbooklist.php' ) && current_user_can( 'activate_plugins' ) ) {

		// Stop activation redirect and show error.
		wp_die( 'Whoops! This WPBookList Extension requires the Core WPBookList Plugin to be installed and activated! <br><a target="_blank" href="https://wordpress.org/plugins/wpbooklist/">Download WPBookList Here!</a><br><br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
	}
}

// Adding the front-end ui css file for this extension
function wpbooklist_jre_goodreads_frontend_ui_style() {
    wp_register_style( 'wpbooklist-goodreads-frontend-ui', GOODREADS_ROOT_CSS_URL.'goodreads-frontend-ui.css' );
    wp_enqueue_style('wpbooklist-goodreads-frontend-ui');
}

// Code for adding the general admin CSS file
function wpbooklist_jre_goodreads_admin_style() {
  if(current_user_can( 'administrator' )){
      wp_register_style( 'wpbooklist-goodreads-admin-ui', GOODREADS_ROOT_CSS_URL.'goodreads-admin-ui.css');
      wp_enqueue_style('wpbooklist-goodreads-admin-ui');
  }
}


function goodreads_single_cron_function( $table_name_from, $goodreadsSelection, $append, $page_yes, $post_yes, $woocommerce ) {

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



?>