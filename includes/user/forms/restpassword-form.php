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
}
?>
	<form name="resetpasswordform" id="resetpasswordform" action="" method="post">
		<p>
			<label for="pass1"><?php _e( 'New password' ); ?></label>
			<input autocomplete="off" name="pass1" id="pass1" class="input" size="20" value="" type="password" autocomplete="off" />
		</p>

		<p>
			<label for="pass2"><?php _e( 'Confirm new password' ); ?></label>
			<input autocomplete="off" name="pass2" id="pass2" class="input" size="20" value="" type="password" autocomplete="off" />
		</p>

		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" value="<?php esc_attr_e( 'Reset Password' ); ?>" />
			<input type="hidden" name="key" value="<?php echo $_REQUEST['key'] ?>" />
			<input type="hidden" name="login" id="user_login" value="<?php echo $_REQUEST['login'] ?>" />
			<input type="hidden" name="action" value="resetpass" />
		</p>
	</form>
</div>
