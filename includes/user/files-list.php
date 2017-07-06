<?php
  $logout_url = '<a href="' . wp_logout_url(home_url()) . '" title="Logout">'.__('Logout', 'user-file-upload-v5').'</a>';
  $bhu_uufef = get_user_meta($current_user->ID, 'user_file_uploads', true);
?>
<div>
  <div class="grid">
    <div class="grid__item one-half">
      <h3 class="beta files-title"><?php echo __('Your files','user-file-upload-v5'); ?></h3>
    </div><div class="grid__item one-half">
      <div class="text--end">
        <span><?php echo __('Welcome', 'user-file-upload-v5'); ?>, <b><?php echo $current_user->user_login ?></b></span>
        <span class="horizontal-separator"></span>
        <span><?php echo $logout_url; ?></span>
      </div>
    </div>
  </div>
  <?php
    if (isset($bhu_uufef['file_url']) && $extra_fields = array_filter($bhu_uufef['file_url'])) {
  ?>
    <div class="soft--top">
      <?php
        foreach ($extra_fields as $key => $value) {
          $wpnonce = wp_create_nonce("bhu_" . $bhu_uufef['file_id'][$key]);
          $size = size_format(filesize(get_attached_file($bhu_uufef['file_id'][$key])));
          $download_link = "<a class='dark-link' href='" . BHUUFU_URL . 'download.php?id=' . $bhu_uufef['file_id'][$key] . "&amp;wpnonce=$wpnonce' title='".__('Download', 'user-file-upload-v5')."'>".__('Download', 'user-file-upload-v5')."</a>";
      ?>
      <div class="user-file">
        <div class="grid grid--full">
          <div class="grid__item medium--four-twelfths">
            <img class="user-list-image" src="<?php echo BHUUFU_URL; ?>assets/images/file-icon.png" srcset="<?php echo BHUUFU_URL; ?>assets/images/file-icon@2x.png 2x" alt="File">
            <span class="user-list-filename"><?php echo $bhu_uufef['file_oname'][$key];?></b>
          </div><div class="grid__item medium--six-twelfths">
            <span class="user-list-description"><?php echo $bhu_uufef['file_description'][$key]; ?></span>
          </div><div class="grid__item medium--two-twelfths text--end">
            <?php echo $download_link; ?>
          </div>
        </div>
      </div>
      <?php
        }
      ?>
    </div>
    <h4 class="text--center files-advice"><?php echo __('Thanks for trusting Kreative!','user-file-upload-v5'); ?></h4>
  </div>
  <?php
    }
    else {
  ?>
  <h4 class="text--center files-advice">
    <?php echo __('There are no files available. Do you search for any content?','user-file-upload-v5'); ?>
    <a href="/contact"><?php echo __('Contact us','user-file-upload-v5'); ?></a>
  </h4>
  <?php
    }
  ?>
</div>