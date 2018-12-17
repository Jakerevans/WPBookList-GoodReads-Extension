<?php
/**
 * WPBookList WPBookList_Goodreads_Form Submenu Class
 *
 * @author   Jake Evans
 * @category ??????
 * @package  ??????
 * @version  1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WPBookList_Goodreads_Form', false ) ) :
/**
 * WPBookList_Goodreads_Form Class.
 */
class WPBookList_Goodreads_Form {

	public static function output_goodreads_form(){
		global $wpdb;
		// Getting all user-created libraries
		$table_name = $wpdb->prefix . 'wpbooklist_jre_list_dynamic_db_names';
		$db_row = $wpdb->get_results("SELECT * FROM $table_name");

		$string1 = '<div id="wpbooklist-goodreads-area">
						<select id="wpbooklist_select_backup_box" name="cars">    
    						<option selected="" disabled="">Select a Goodreads File</option>';

            $string2 = '';
            foreach(glob(GOODREADS_UPLOAD_DIR.'*.*') as $filename){
                $orig_filename = basename($filename);
                $filename = basename($filename);
                $filename = str_replace('_library_', ' ', $filename);
                $filename = str_replace('.csv', '', $filename);
                $filename = ucfirst($filename);
                $display_name = explode('_-_', $filename);
                $string2 = $string2.'<option class="wpbooklist-goodreads-actual-option" id="'.$filename.'" value="'.$orig_filename.'">'.$display_name[0].' '.$display_name[1].' - '.date('h:i a', $display_name[2]).'</option>';
            }




    $string3 = '</select>
    				<select id="wpbooklist_goodreads_library_box">
							<option selected disabled>Select Library to Apply File to&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
							<option value="'.$wpdb->prefix.'wpbooklist_jre_saved_book_log">Default Library</option>';
		
		$string4 = '';
		foreach($db_row as $db){
			if(($db->user_table_name != "") || ($db->user_table_name != null)){
				$string4 = $string4.'<option value="'.$wpdb->prefix.'wpbooklist_jre_'.$db->user_table_name.'">'.ucfirst($db->user_table_name).'</option>';
			}
		}

  		$string5 = '</select>
  					<div style="margin-top:30px; margin-bottom:15px;">
      				<label>Append to selected library?</label>
      				<input id="wpbooklist-jre-goodreads-append" name="wpbooklist-jre-goodreads-append" type="checkbox">      
      				<div style="margin:10px; font-weight:bold;"> Or </div>
      				<label>Overwrite selected library?</label>
      				<input id="wpbooklist-jre-goodreads-overwrite" name="wpbooklist-jre-goodreads-overwrite" type="checkbox">
              <div id="wpbooklist-create-page-post-goodreads-div">
                <label>Create a Page for Each Book?</label>
                <input id="wpbooklist-jre-goodreads-create-page" name="wpbooklist-jre-goodreads-create-page" type="checkbox">      
                <label>Create a Post for Each Book?</label>
                <input id="wpbooklist-jre-goodreads-create-post" name="wpbooklist-jre-goodreads-create-post" type="checkbox">      
              </div>';

              // Check to see if Storefront extension is active
              include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
              if(is_plugin_active('wpbooklist-storefront/wpbooklist-storefront.php')){

                $string5 = $string5.'<div id="wpbooklist-create-woo-goodreads-div">
                  <label>Create a WooCommerce Product for Each Book?</label>
                  <input id="wpbooklist-jre-goodreads-create-woo" name="wpbooklist-jre-goodreads-create-woo" type="checkbox">        
                </div>';
              }

            $string5 = $string5.'</div>
              	<button disabled="disabled" id="wpbooklist-submit-goodreads" name="add-goodreads" type="button">Import Selected Goodreads File</button>
              	<p style="display:inline-block; font-weight:bold; margin:25px 10px 0px 10px;"> Or </p>
                <input id="wpbooklist-add-new-goodreads-file" style="display:none;" type="file" name="files[]" multiple="">
                <button onclick="document.getElementById(\'wpbooklist-add-new-goodreads-file\').click();" name="add-goodreads-file" type="button">Add a New Goodreads File</button>
                <div class="wpbooklist-spinner" id="wpbooklist-spinner-1"></div>
                <div id="wpbooklist-addgoodreads-success-div"></div>
              	<p style="font-style:italic;">View the video below for instructions on how to obtain your Goodreads export. For best results, do not open your Goodreads export; simply download it and immediately import it using the \'Add a New Goodreads File\' button above.<br><br>The Upload process can take quite some time depending on the size of your Goodreads library.</p>
              	<iframe width="540" height="260" src="https://www.youtube.com/embed/PG5XmwOb5uA" frameborder="0" allowfullscreen=""></iframe>
            </div>';

    		return $string1.$string2.$string3.$string4.$string5;
	}
}

endif;