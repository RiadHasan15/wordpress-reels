<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="bpr-upload-form">
  <input type="hidden" name="action" value="bpr_upload_reel">
  <?php wp_nonce_field('bpr_upload_reel'); ?>
  <p><label>Title<br><input type="text" name="bpr_title" required maxlength="100"></label></p>
  <p><label>Description<br><textarea name="bpr_description" maxlength="300"></textarea></label></p>
  <p><label>Video (MP4)<br><input type="file" name="bpr_video" accept="video/mp4,video/webm,video/mov" required></label></p>
  <p><button type="submit" class="button button-primary">Upload Reel</button></p>
</form>
