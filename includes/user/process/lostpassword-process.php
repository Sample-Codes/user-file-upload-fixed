<?php
$http_post = ( 'POST' == $_SERVER['REQUEST_METHOD'] );
global $post ;
$redirect_to = get_page_link($post->ID);
if (  $http_post  ) {
    global $wpdb, $current_site;

    $errors = new WP_Error();

    if ( empty( $_POST['log'] ) ) {
            $errors->add( 'empty_username', __( '<strong>ERROR</strong>: Enter a username or e-mail address.' ) );
    } else if ( strpos( $_POST['log'], '@' ) ) {
            $user_data = get_user_by( 'email', trim( $_POST['log'] ) );
            if ( empty( $user_data ) )
                    $errors->add( 'invalid_email', __( '<strong>ERROR</strong>: There is no user registered with that email address.' ) );
    } else {
            $login = trim( $_POST['log'] );
            $user_data = get_user_by( 'login', $login );
    }

    if ( $errors->get_error_code() )
    $this->errors = $errors;

    if ( ! is_wp_error( $this->errors) ){

        if ( ! $user_data ) {
                $errors->add( 'invalidcombo', __( '<strong>ERROR</strong>: Invalid username or e-mail.' ) );
                $this->errors = $errors; 
        }
        if ( ! is_wp_error( $this->errors) ){
            $user_login = $user_data->user_login;
            $user_email = $user_data->user_email;

            $key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login ) );
            if ( empty( $key ) ) {
                    // Generate something random for a key...
                    $key = wp_generate_password( 20, false );
                    do_action( 'retrieve_password_key', $user_login, $key );
                    // Now insert the new md5 key into the db
                    $wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user_login ) );
            }

            $message = __( 'Someone requested that the password be reset for the following account:' ) . "\r\n\r\n";
            $message .= network_home_url( '/' ) . "\r\n\r\n";
            $message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
            $message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
            $message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
            $message .= '<' .  "$redirect_to?form=resetpassword&do_process=resetpassword&key=$key&login=" . rawurlencode( $user_login ) . ">\r\n";

            // The blogname option is escaped with esc_html on the way into the database in sanitize_option
            // we want to reverse this for the plain text arena of emails.
            $blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

            $title = sprintf( __( '[%s] Password Reset' ), $blogname );

            $title = apply_filters( 'retrieve_password_title', $title, $user_data->ID );
            $message = apply_filters( 'retrieve_password_message', $message, $key, $user_data->ID );

            if ( $message && ! wp_mail( $user_email, $title, $message ) )
                    wp_die( __( 'The e-mail could not be sent.' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...' ) );
            $this->errors = "sucess";
        }
    }
}
?>