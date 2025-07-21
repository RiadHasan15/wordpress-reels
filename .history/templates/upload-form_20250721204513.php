<form method="post" action="<?php echo admin_url('admin-post.php');?>" enctype="multipart/form-data" class="bpr-upload-form">
    <input type="hidden" name="action" value="bpr_upload_reel">
    <?php wp_nonce_field('bpr_upload_nonce');?>
    <p><label>Title<br><input type="text" name="bpr_title" required></label></p>
    <p><label>Description<br><textarea name="bpr_description"></textarea></label></p>
    <p><label>Video (mp4)<br><input type="file" name="bpr_video" accept="video/mp4" required></label></p>
    <p><button type="submit" class="button button-primary">Upload</button></p>
</form>
