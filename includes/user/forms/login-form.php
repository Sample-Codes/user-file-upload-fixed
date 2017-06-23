<div class="useruploadforms">
<?php
global $post,$error;

	$url = get_page_link($post->ID);
	$query = parse_url($url, PHP_URL_QUERY);

	// Returns a string if the URL has parameters or NULL if not
	if ($query) {
		$url .= '&';
	} else {
		$url .= '?';
	}

if (is_wp_error($this->errors)){
        
    $errors = $this->errors;
	//print_r($errors->errors);
	$data = key($errors->errors);
	
	
	// print_r($errors->errors);
	
            if ( $data == "invalid_username" ) {
                echo "<p class='red'><strong>ERROR:</strong> Invalid username.<a href='". $url . "form=lostpassword' > Lost your password ?</a></p>";
            } elseif ( $data == "incorrect_password" ) {
                echo "<p class='red'><strong>ERROR:</strong> The password is incorrect.<a href='". $url ."form=lostpassword' > Lost your password ?</a></p>";
            } else {
				foreach ($errors->errors as $datas) {
                echo "<p class='red'>" . $datas[0] . "</p>";
				}
			}
   
} elseif( $_GET['resetpass'] == "complete"){
    echo "<p class='green'>Password sucessfully changed.Now try to login again </p>";
}

do_action( 'login_head' );
 if ( $error ) {
     echo "<p class='red'>$error</p>";
 }

?>
<form name="loginform" id="loginform" action="" method="post">

    <p class="login-username">
            <label for="user_login">Username</label>
            <input type="text" name="log" id="user_login" class="input" value="" size="20">
    </p>
    <p class="login-password">
            <label for="user_pass">Password</label>
            <input type="password" name="pwd" id="user_pass" class="input" value="" size="20">
    </p>
    <p class="login-submit">
            <input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="Log In">
            <input type="hidden" name="redirect_to" value="<?php echo get_page_link($post->ID) ; ?>">
    </p>
	
	<p>
	<?php echo "<a href='".$url."form=lostpassword' > Lost your password ?</a>"; ?>
	</p>

</form>
</div>