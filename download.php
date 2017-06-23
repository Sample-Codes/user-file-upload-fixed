<?php
global $current_user ;
//get all variables
$file_id = $_REQUEST['id'] ;
$wp_nonce =  $_REQUEST['wpnonce'];

//get site base path
$path_to_file = dirname(dirname(__FILE__)) . '\\';
$content_dir = 'wp-content';
$wp_dir = '';
if(strpos($path_to_file, 'app')) {
  $content_dir = 'app';
  $wp_dir = 'wp/';
}
$site_base_tmp = explode($content_dir, $path_to_file);
$site_base = $site_base_tmp[0];

require_once($site_base . $wp_dir .'wp-load.php');

if ( $wp_nonce && $file_id ) {
    
    $home_url = home_url();
    $status = 200;
	
    if( ! wp_verify_nonce( $wp_nonce,"bhu_".$file_id ) ) {
            header("refresh:5;$home_url", true, $status); 
            echo '<h1>You don\'t have access to this file.You\'ll be redirected in about 5 secs.</h1>';
            exit;
    }

    //build file path
    $bhu_uufef = get_user_meta($current_user->ID, 'user_file_uploads',true);
    $key = array_search($file_id, $bhu_uufef['file_id']);
    $filename = $bhu_uufef['file_oname'][$key] ;
    $mime = $bhu_uufef['file_mime'][$key] ;
    $filepath = UPLOADS_DIR."/user-files/".$filename;

    if( ! is_file( $filepath ) ) {
            header("refresh:5;$home_url", true, $status); 
            echo '<h1>Requested file not found.You\'ll be redirected in about 5 secs.</h1>';
            exit;
    }

    //see http://php.net/file_get_contents
    //$data = file_get_contents($filepath);

    // see http://php.net/strlen
    //$size = strlen($data);
     $size = filesize($filepath);

    //trigger HTTP request - see http://php.net/header
    header("Content-Disposition: attachment; filename = $filename"); 
    header("Content-Length: $size");
    header("Content-Type: $mime");
        //see http://php.net/echo
    readfile($filepath); 
  
 } else {
    header("refresh:5;$home_url", true, $status); 
    echo '<h1>You don\'t have access to this file.You\'ll be redirected in about 5 secs.</h1>';
    exit;
 }
 
?>
