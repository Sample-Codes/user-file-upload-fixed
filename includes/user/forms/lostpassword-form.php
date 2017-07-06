<div class="centered-box">
  <h3 class="zeta story-title text--center"><?php echo __('Forgot password?','user-file-upload-v5'); ?></h3>
  <form name="lostpasswordform" action="" method="post" class="centered-box__content soft--top">
    <input type="hidden" name="redirect_to" value="<?php get_page_link($post->ID)."?form=lostpassword"; ?>" />
    <input type="hidden" name="do_process" value="lostpassword" />
    <p class="text--start"><?php echo __('Please enter your username or email address. You will receive a link to create a new password via email.','user-file-upload-v5'); ?></p>
    <label class="login-label" for="log"><?php echo __( 'Username or E-mail:','user-file-upload-v5' ); ?></label>
    <input type="text" name="log" class="form-control wpcf7-form-control wpcf7-text wpcf7-text--full" value="" placeholder="<?php echo __('Enter your username or e-mail','user-file-upload-v5'); ?>" />
    <div class="push--top text--start">
      <input type="submit" class="button button--primary" name="wp-submit" value="<?php echo __( 'Get New Password' ); ?>" />
    </div>
  </form>
  <div class="push--top">
    <?php
      global $post ;
      if (is_wp_error($this->errors)) {
        $errors = $this->errors;
        foreach ( $errors as $error) {
          foreach ( $error as $data => $error_single) {
            if ( $data == "incorrect_password" ) {
              echo "<p class='text--red'><strong>ERROR:</strong>".__('The credentials are wrong').".<a href='".get_page_link($post->ID)."?form=lostpassword' > Password Lost ?</a></p>";
            }
            else {
              echo "<p class='text--red'>$error_single[0]</p>";
            }
          }
        }
      }
      else if( $this->errors == "sucess" ) {
        echo "<p>".__('Instructions to reset your password have been sent to you. Please check your email.','user-file-upload-v5')."</p>";
      }
      else if( isset($_REQUEST['error']) && $_REQUEST['error'] == "invalidkey" ) {
        echo "<p class='text--red'>".__('Invalid Key').".</p>";
      }
    ?>
  </div>
</div>