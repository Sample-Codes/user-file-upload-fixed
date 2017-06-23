<?php
//login process
if ( isset($_POST['wp-submit']) && $_POST['wp-submit'] = "Log In" ) {
    $redirect_to = $_REQUEST['redirect_to'];
    $secure_cookie = '';

    // If the user wants ssl but the session is not ssl, force a secure cookie.
    if ( !empty($_POST['log']) && !force_ssl_admin() ) {
            $user_name = sanitize_user($_POST['log']);
            if ( $user = get_user_by('login', $user_name) ) {
                    if ( get_user_option('use_ssl', $user->ID) ) {
                            $secure_cookie = true;
                            force_ssl_admin(true);
                    }
            }
    }

    $reauth = empty($_REQUEST['reauth']) ? false : true;

    // If the user was redirected to a secure login form from a non-secure admin page, and secure login is required but secure admin is not, then don't use a secure
    // cookie and redirect back to the referring non-secure admin page. This allows logins to always be POSTed over SSL while allowing the user to choose visiting
    // the admin via http or https.
    if ( !$secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )
            $secure_cookie = false;

    // If cookies are disabled we can't log in even with a valid user+pass
    if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
            $user = new WP_Error('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));
    else
            $user = wp_signon('', $secure_cookie);

    if ( !is_wp_error($user) && !$reauth ) {
            wp_safe_redirect($redirect_to);
            exit();
    }
    
//    global $error;
//    do_action( 'login_head' );
//    if ( !empty( $error ) ) {
//        $user->add('error', $error);
//        unset($error);
//    }
    
    $this->errors = $user;
    
}
?>