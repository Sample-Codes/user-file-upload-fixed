<?php
/**
 * Plugin Name: User File Upload
 * Description: User File Upload Manager
 * Version: 1.5
 * Author: James Willson
 * License: GPL2
 */
 
/*
 * BHUUFU  --  BHUUFU
 * bhuufu  - bhuufu
 * User File Upload  -- User File Upload 
 * user-file-upload -- user-file-upload
 */

 // Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

/*
 * Global constants
 */
define( 'BHUUFU_PLUGIN_FILE', __FILE__ );	   // /path/to/wp-content/plugins/pintrestpopup/index.php
define( 'BHUUFU_PATH', plugin_dir_path(__FILE__) );  // /path/to/wp-content/plugins/pintrestpopup
define( 'BHUUFU_URL', plugin_dir_url( __FILE__ ) );  // http://www.domain.com/wp-content/plugins/pintrestpopup
define( 'BHUUFU_PLUGIN_NAME', 'User File Upload ' );
define( 'BHUUFU_PLUGIN_SLUG', 'user-file-upload' );
define( 'BHUUFU_LNG', 'user_file_upload_LNG' );
define( 'BHUUFU_VERSION', 1.0 );
define( 'UPLOADS_DIR', wp_upload_dir()['basedir'] );


// Required Files
include_once('includes/tests/tests.php');
include_once('includes/admin/bhuufu_admin.php');
//include_once('includes/load.php');

function plugin_install_function()
  {
    //post status and options
    $addpost = array(
          'comment_status' => 'closed',
          'ping_status' =>  'closed' ,
          'post_author' => 1,
          'post_date' => date('Y-m-d H:i:s'),
          'post_name' => 'User files',
          'post_status' => 'publish' ,
          'post_title' => 'User files',
          'post_type' => 'page',
		  'post_content' => '[list_user_files]',
    );  
    //insert page and save the id
    $newvalue = wp_insert_post( $addpost, false );
    //save the id in the database
    update_option( 'filespage', $newvalue );
}

function plugin_uninstall_function()
  {
  
  $unp = get_page_by_title( 'User files' );
  wp_delete_post($unp->ID,true);
  
  }
  
register_activation_hook( __FILE__, 'plugin_install_function');
register_deactivation_hook( __FILE__, 'plugin_uninstall_function' );
  
add_action( 'admin_menu', 'bhuufu_add_admin_menu' );
add_action( 'admin_init', 'bhuufu_settings_init' );


function bhuufu_add_admin_menu(  ) { 

	add_options_page( 'User File Upload', 'User File Upload', 'manage_options', 'user_file_upload', 'bhuufu_options_page' );

}


function bhuufu_settings_init(  ) { 

	register_setting( 'pluginPage', 'bhuufu_settings' );

	add_settings_section(
		'bhuufu_pluginPage_section', 
		__( 'Shortcode page', 'wordpress' ), 
		'bhuufu_settings_section_callback', 
		'pluginPage'
	);

	add_settings_field( 
		'filespage', 
		__( 'Shortcode page', 'wordpress' ), 
		'filespage_render', 
		'pluginPage', 
		'bhuufu_pluginPage_section' 
	);


}


function filespage_render(  ) { 

	$options = get_option( 'bhuufu_settings' );

	echo "<select name='bhuufu_settings[filespage]'>";
 
 $pages = get_pages(); 
  foreach ( $pages as $page ) {
  	$option = '<option value="' . $page->ID . '" '. selected( $options['filespage'], $page->ID ) .'>';
	$option .= $page->post_title;
	$option .= '</option>';
	echo $option;
  }

  echo "</select> ";
  
}


function bhuufu_settings_section_callback(  ) { 

	echo __( 'Please select the page where the shortcode is located. The shortcode [list_user_files] must be added on a page in order to display the files.', 'wordpress' );

}


function bhuufu_options_page(  ) { 


	echo "<form action='options.php' method='post'>";
		
		echo "<h2>User File Upload</h2>";
		
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();

		
	echo "</form>";

}