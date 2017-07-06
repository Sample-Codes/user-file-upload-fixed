<div class="centered-box">
  <h3 class="zeta story-title text--center"><?php echo __('New password'); ?></h3>
  <form name="resetpasswordform" id="resetpasswordform" action="" method="post" class="centered-box__content soft--top">
    <div class="push-half--bottom">
      <label for="pass1"><?php echo __( 'New password' ); ?></label>
      <input autocomplete="off" name="pass1" id="pass1" class="form-control wpcf7-form-control wpcf7-text wpcf7-text--full" value="" type="password" />
    </div>
    <div class="push-half--bottom">
      <label for="pass1"><?php echo __( 'Confirm new password' ); ?></label>
      <input autocomplete="off" name="pass2" id="pass2" class="form-control wpcf7-form-control wpcf7-text wpcf7-text--full" value="" type="password" />
    </div>
    <div class="push-half--bottom">
      <input type="submit" name="wp-submit" id="wp-submit" class="button button--primary" value="<?php __( 'Reset Password' ); ?>" />
      <input type="hidden" name="key" value="<?php echo $_REQUEST['key'] ?>" />
      <input type="hidden" name="login" id="user_login" value="<?php echo $_REQUEST['login'] ?>" />
      <input type="hidden" name="action" value="resetpass" />
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
              echo "<p class='red'><strong>ERROR:</strong>".__('The credentials are wrong','user-file-upload-v5').".<a href='".get_page_link($post->ID)."?form=lostpassword' > Password Lost ?</a></p>";
            }
            else {
              echo "<p class='red'>$error_single[0]</p>";
            }
          }
        }
      }
    ?>
  </div>
</div>