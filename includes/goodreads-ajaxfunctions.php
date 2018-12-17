<?php

// Function to upload a new Goodreads Export
function wpbooklist_goodreads_add_new_action_javascript() { 
	?>
  	<script type="text/javascript" >
  	"use strict";
  	jQuery(document).ready(function($) {

  		// Toggle of the checkboxes
		$('#wpbooklist-jre-goodreads-overwrite').change(function(){
	  		if ($(this).is(':checked')) {
	      		$('[name=wpbooklist-jre-goodreads-append]').prop('checked', false);
	  		}
		});
		$('#wpbooklist-jre-goodreads-append').change(function(){
			if ($(this).is(':checked')) {
			  $('[name=wpbooklist-jre-goodreads-overwrite]').prop('checked', false);
			}
		});


		$(document).on("change","#wpbooklist-add-new-goodreads-file", function(evt){

			$('.wpbooklist-spinner').animate({'opacity':'1'});
			$('#wpbooklist-addgoodreads-success-div').html('');

			var files = evt.target.files; // FileList object
		    var theFile = files[0];
		    // Open Our formData Object
		    var formData = new FormData();
		    formData.append('action', 'wpbooklist_goodreads_add_new_action');
		    formData.append('my_uploaded_file', theFile);
		    var nonce = '<?php echo wp_create_nonce( "wpbooklist_goodreads_add_new_action_callback" ); ?>';
		    formData.append('security', nonce);

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: formData,
				contentType:false,
				processData:false,
				success: function(response){
					$('.wpbooklist-spinner').animate({'opacity':'0'});
					$('html, body').animate({
				        scrollTop: $("#wpbooklist-addgoodreads-success-div").offset().top-100
				    }, 1000);

					if(response == 1){
						$('#wpbooklist-addgoodreads-success-div').html('<span id="wpbooklist-add-book-success-span">Success!</span><br/><br/> You\'ve added a new GoodReads Export!<div id="wpbooklist-addstylepak-success-thanks">Thanks for using WPBooklist! If you happen to be thrilled with WPBookList, then by all means, <a id="wpbooklist-addbook-success-review-link" href="https://wordpress.org/support/plugin/wpbooklist/reviews/?filter=5">Feel Free to Leave a 5-Star Review Here!</a><img id="wpbooklist-smile-icon-1" src="http://evansclienttest.com/wp-content/plugins/wpbooklist/assets/img/icons/smile.png"></div>');
					} else {
						$('#wpbooklist-addgoodreads-success-div').html('<span id="wpbooklist-add-book-success-span">Uh-Oh!</span><br/><br/>Looks like there was a problem uploading your GoodReads Export! Please make sure you haven\'t changed the name of the GoodReads Export (it should be something every similar to \'goodreads_library_export.csv\'), and try again.');
					}
					console.log(response)
					//document.location.reload();
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(errorThrown);
		            console.log(textStatus);
		            console.log(jqXHR);
				}
			}); 

			evt.preventDefault ? evt.preventDefault() : evt.returnValue = false;

	  	});
	});
	</script>
	<?php
}

// Callback function for creating backups
function wpbooklist_goodreads_add_new_action_callback(){
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


function wpbooklist_goodreads_restore_actual_action_javascript() { 
	?>
  	<script type="text/javascript" >
  	"use strict";
  	jQuery(document).ready(function($) {

  		// For controlling the disabling of the 'Import Selected Goodreads File' button
  		$("#wpbooklist_select_backup_box").change(function(){
			var fileSelect = $("#wpbooklist_select_backup_box").val();
			var libSelect = $("#wpbooklist_goodreads_library_box").val();
			var appendChecked = $('[name=wpbooklist-jre-goodreads-append]').prop('checked');
			var overwriteChecked = $('[name=wpbooklist-jre-goodreads-overwrite]').prop('checked');
			if((fileSelect != null) && (libSelect != null) &&  ((appendChecked == true) || (overwriteChecked == true))){
			  $("#wpbooklist-submit-goodreads").attr('disabled', false);
			}else {
			  $("#wpbooklist-submit-goodreads").attr('disabled', true);
			}
		});

		$("#wpbooklist_goodreads_library_box").change(function(){
			var fileSelect = $("#wpbooklist_select_backup_box").val();
			var libSelect = $("#wpbooklist_goodreads_library_box").val();
			var appendChecked = $('[name=wpbooklist-jre-goodreads-append]').prop('checked');
			var overwriteChecked = $('[name=wpbooklist-jre-goodreads-overwrite]').prop('checked');
			if((fileSelect != null) && (libSelect != null) &&  ((appendChecked == true) || (overwriteChecked == true))){
			  $("#wpbooklist-submit-goodreads").attr('disabled', false);
			}else {
			  $("#wpbooklist-submit-goodreads").attr('disabled', true);
			}
		});

		$('[name=wpbooklist-jre-goodreads-append]').change(function(){
			var fileSelect = $("#wpbooklist_select_backup_box").val();
			var libSelect = $("#wpbooklist_goodreads_library_box").val();
			var appendChecked = $('[name=wpbooklist-jre-goodreads-append]').prop('checked');
			var overwriteChecked = $('[name=wpbooklist-jre-goodreads-overwrite]').prop('checked');
			if((fileSelect != null) && (libSelect != null) &&  ((appendChecked == true) || (overwriteChecked == true))){
			  $("#wpbooklist-submit-goodreads").attr('disabled', false);
			}else {
			  $("#wpbooklist-submit-goodreads").attr('disabled', true);
			}
		});

		$('[name=wpbooklist-jre-goodreads-overwrite]').change(function(){
			var fileSelect = $("#wpbooklist_select_backup_box").val();
			var libSelect = $("#wpbooklist_goodreads_library_box").val();
			var appendChecked = $('[name=wpbooklist-jre-goodreads-append]').prop('checked');
			var overwriteChecked = $('[name=wpbooklist-jre-goodreads-overwrite]').prop('checked');
			if((fileSelect != null) && (libSelect != null) &&  ((appendChecked == true) || (overwriteChecked == true))){
			  $("#wpbooklist-submit-goodreads").attr('disabled', false);
			} else {
			  $("#wpbooklist-submit-goodreads").attr('disabled', true);
			}
		});

	  	$("#wpbooklist-submit-goodreads").click(function(event){

	  		$('.wpbooklist-spinner').animate({'opacity':'1'});
	  		$('#wpbooklist-addgoodreads-success-div').html('');

	  		var fileName = jQuery("#wpbooklist_select_backup_box").val();
		    var library = jQuery("#wpbooklist_goodreads_library_box").val();
		    var appendChecked = jQuery('[name=wpbooklist-jre-goodreads-append]').prop('checked');
		    var overwriteChecked = jQuery('[name=wpbooklist-jre-goodreads-overwrite]').prop('checked');
		    var pageyes = jQuery('#wpbooklist-jre-goodreads-create-page').prop('checked');
		    var postyes = jQuery('#wpbooklist-jre-goodreads-create-post').prop('checked');
		    var woocommerce = jQuery('#wpbooklist-jre-goodreads-create-woo').prop('checked');

		    var howToUpload;
		    if(appendChecked == true){
		      howToUpload = 'append';
		    } else{
		      howToUpload = 'overwrite';
		    }

		  	var data = {
				'action': 'wpbooklist_goodreads_restore_actual_action',
				'security': '<?php echo wp_create_nonce( "wpbooklist_goodreads_restore_actual_action_callback" ); ?>',
				'filename' : fileName,
          		'library' : library,
          		'howtoupload' : howToUpload,
          		'pageyes':pageyes,
          		'postyes':postyes,
          		'woocommerce':woocommerce
			};
			console.log(data);

	     	var request = $.ajax({
			    url: ajaxurl,
			    type: "POST",
			    data:data,
			    timeout: 0,
			    success: function(response) {
			    	if(response == 1){
			    		$('#wpbooklist-addgoodreads-success-div').html('<span id="wpbooklist-add-book-success-span">Success!</span><br/><br/> Your Goodreads Export is uploading! Be patient though - it can take quite some time for the upload to complete.<div id="wpbooklist-addstylepak-success-thanks">Thanks for using WPBooklist! If you happen to be thrilled with WPBookList, then by all means, <a id="wpbooklist-addbook-success-review-link" href="https://wordpress.org/support/plugin/wpbooklist/reviews/?filter=5">Feel Free to Leave a 5-Star Review Here!</a><img id="wpbooklist-smile-icon-1" src="http://evansclienttest.com/wp-content/plugins/wpbooklist/assets/img/icons/smile.png"></div>');
			    	}
			    	$('.wpbooklist-spinner').animate({'opacity':'0'});
			    	console.log(response);
			    },
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(errorThrown);
		            console.log(textStatus);
		            console.log(jqXHR);
				}
			});

			event.preventDefault ? event.preventDefault() : event.returnValue = false;
	  	});
	});
	</script>
	<?php
}

// Callback function for creating backups
function wpbooklist_goodreads_restore_actual_action_callback(){
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


/*
 * Below is a goodreads ajax function and callback, 
 * complete with console.logs and echos to verify functionality
 */

/*
// For adding a book from the admin dashboard
add_action( 'admin_footer', 'wpbooklist_goodreads_action_javascript' );
add_action( 'wp_ajax_wpbooklist_goodreads_action', 'wpbooklist_goodreads_action_callback' );
add_action( 'wp_ajax_nopriv_wpbooklist_goodreads_action', 'wpbooklist_goodreads_action_callback' );


function wpbooklist_goodreads_action_javascript() { 
	?>
  	<script type="text/javascript" >
  	"use strict";
  	jQuery(document).ready(function($) {
	  	$("#wpbooklist-admin-addbook-button").click(function(event){

		  	var data = {
				'action': 'wpbooklist_goodreads_action',
				'security': '<?php echo wp_create_nonce( "wpbooklist_goodreads_action_callback" ); ?>',
			};
			console.log(data);

	     	var request = $.ajax({
			    url: ajaxurl,
			    type: "POST",
			    data:data,
			    timeout: 0,
			    success: function(response) {
			    	console.log(response);
			    },
				error: function(jqXHR, textStatus, errorThrown) {
					console.log(errorThrown);
		            console.log(textStatus);
		            console.log(jqXHR);
				}
			});

			event.preventDefault ? event.preventDefault() : event.returnValue = false;
	  	});
	});
	</script>
	<?php
}

// Callback function for creating backups
function wpbooklist_goodreads_action_callback(){
	global $wpdb;
	check_ajax_referer( 'wpbooklist_goodreads_action_callback', 'security' );
	//$var1 = filter_var($_POST['var'],FILTER_SANITIZE_STRING);
	//$var2 = filter_var($_POST['var'],FILTER_SANITIZE_NUMBER_INT);
	echo 'hi';
	wp_die();
}*/




?>