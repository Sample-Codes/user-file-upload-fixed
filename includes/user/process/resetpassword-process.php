<?php
global $post ;
$redirect_to = get_page_link($post->ID);

$user = self::check_password_reset_key($_REQUEST['key'], $_REQUEST['login']);

if (is_wp_error($user)) {
    $redirect_to = $redirect_to.'?form=lostpassword&error=invalidkey';
    wp_redirect($redirect_to);
    exit;
}
    $errors = new WP_Error();
if (isset($_POST['pass1']) && empty($_POST['pass1']) && isset($_POST['pass2']) && empty($_POST['pass2'])) { 
    $errors->add('password_empty', __('The passwords are empty.'));
} elseif (isset($_POST['pass1']) && $_POST['pass1'] != $_POST['pass2']) {
    $errors->add('password_reset_mismatch', __('The passwords do not match.'));
} elseif (isset($_POST['pass1']) && !empty($_POST['pass1'])) {
    self::reset_password($user, $_POST['pass1']);

    $redirect_to = $redirect_to.'?resetpass=complete';
    wp_safe_redirect($redirect_to);
    exit;
}
if ( $errors->get_error_code() )
    $this->errors = $errors;
?>