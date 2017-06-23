<div class="useruploadforms">
<?php
global $post ;
if (is_wp_error($this->errors)){
    $errors = $this->errors;
    foreach ( $errors as $error){
        foreach ( $error as $data => $error_single){
            if ( $data == "incorrect_password" ) {
                echo "<p class='red'><strong>ERROR:</strong> The credentials are wrong.<a href='".get_page_link($post->ID)."?form=lostpassword' > Password Lost ?</a></p>";
            } else {
                echo "<p class='red'>$error_single[0]</p>";
            }
        }
    }
}else if( $this->errors == "sucess" ){
    echo "<p class='green'>Check your e-mail for the confirmation link.</p>";
}else if( $_REQUEST['error'] == "invalidkey" ){
    echo "<p class='red'>Invalid Key.</p>";
}
?>
	<form name="lostpasswordform" action="" method="post">
            <p>         <p>Please enter your username or email address. You will receive a link to create a new password via email.</p>
			<label for="log"><?php _e( 'Username or E-mail:' ); ?></label>
			<input type="text" name="log"class="input" value="" size="20" />
		</p>

		<p class="submit">
			<input type="submit" name="wp-submit" value="<?php esc_attr_e( 'Get New Password' ); ?>" />
			<input type="hidden" name="redirect_to" value="<?php get_page_link($post->ID)."?form=lostpassword"; ?>" />
			<input type="hidden" name="do_process" value="lostpassword" />
		</p>
	</form>
</div>