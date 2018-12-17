<?php
/**
 * WPBookList Goodreads Tab
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes/Classes
 * @version  1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WPBookList_Goodreads', false ) ) :
/**
 * WPBookList_Admin_Menu Class.
 */
class WPBookList_Goodreads {

    public function __construct() {
        require_once(CLASS_DIR.'class-admin-ui-template.php');
        require_once(GOODREADS_CLASS_DIR.'class-goodreads-form.php');
        // Instantiate the class
        $this->template = new WPBookList_Admin_UI_Template;
        $this->form = new WPBookList_Goodreads_Form;
        $this->output_open_admin_container();
        $this->output_tab_content();
        $this->output_close_admin_container();
        $this->output_admin_template_advert();
    }

    private function output_open_admin_container(){
        $title = 'Goodreads';
        $icon_url = GOODREADS_ROOT_IMG_ICONS_URL.'goodreads.svg';
        echo $this->template->output_open_admin_container($title, $icon_url);
    }

    private function output_tab_content(){
        echo $this->form->output_goodreads_form();
    }

    private function output_close_admin_container(){
        echo $this->template->output_close_admin_container();
    }

    private function output_admin_template_advert(){
        echo $this->template->output_template_advert();
    }


}
endif;

// Instantiate the class
$cm = new WPBookList_Goodreads;