<?php
  global $post,$error;
  $url = get_page_link($post->ID);
  $query = parse_url($url, PHP_URL_QUERY);

  // Returns a string if the URL has parameters or NULL if not
  if ($query) {
    $url .= '&';
  }
  else {
    $url .= '?';
  }
?>
<div class="centered-box">
  <h3 class="zeta story-title text--center"><?php echo __('Client login','user-file-upload-v5'); ?></h3>
  <form name="loginform" id="loginform" action="" method="post" class="centered-box__content soft--top">
    <input type="hidden" name="redirect_to" value="<?php echo get_page_link($post->ID); ?>">
    <div class="push--ends">
      <label class="login-label" for="user_login"><?php echo __('Username'); ?></label>
      <input type="text" name="log" id="user_login" class="form-control wpcf7-form-control wpcf7-text wpcf7-text--full" value="" placeholder="<?php echo __('Enter your username or e-mail','user-file-upload-v5'); ?>">
    </div>
    <div class="push--bottom">
      <label class="login-label" for="user_login"><?php echo __('Password'); ?></label>
      <input type="password" name="pwd" id="user_pass" class="form-control wpcf7-form-control wpcf7-text wpcf7-text--full" value="" placeholder="******">
    </div>
    <div class="grid">
      <div class="grid__item one-half text--start">
        <input type="submit" name="wp-submit" id="wp-submit" class="button button--primary" value="<?php echo __('Login','user-file-upload-v5'); ?>">
      </div><div class="grid__item one-half text--end">
        <div class="push-half--top">
          <a class="dark-link" href="<?php echo $url; ?>form=lostpassword"><?php echo __('Forgot your password?','user-file-upload-v5'); ?></a></div>
        </div>
    </div>
  </form>
  <div class="push--top">
    <?php
      if (is_wp_error($this->errors)) {
        $errors = $this->errors;
        //print_r($errors->errors);
        $data = key($errors->errors);
        // print_r($errors->errors);
        if ( $data == "invalid_username" ) {
          echo "<p class='red'><strong>ERROR:</strong> Invalid username.<a href='". $url . "form=lostpassword' > Lost your password ?</a></p>";
        }
        elseif ( $data == "incorrect_password" ) {
          echo "<p class='red'><strong>ERROR:</strong> The password is incorrect.<a href='". $url ."form=lostpassword' > Lost your password ?</a></p>";
        }
        else {
          foreach ($errors->errors as $datas) {
            echo "<p class='red'>" . $datas[0] . "</p>";
          }
        }
      }
      else if( isset($_GET['resetpass']) && $_GET['resetpass'] == "complete"){
        echo "<p class='green'>Password sucessfully changed.Now try to login again </p>";
      }
      do_action( 'login_head' );
      if ( $error ) {
        echo "<p class='red'>$error</p>";
      }
    ?>
  </div>
</div>