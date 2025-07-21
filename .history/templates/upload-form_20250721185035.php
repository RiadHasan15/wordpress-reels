<?php if (is_user_logged_in()): ?>
<form method="post" enctype="multipart/form-data" class="bpr-upload-form">
    <label>Title</label>
    <input type="text" name="bpr_title" required>
    
    <label>Description</label>
    <textarea name="bpr_description" rows="3" required></textarea>
    
    <label>Video File</label>
    <input type="file" name="bpr_video" accept="video/*" required>

    <input type="submit" name="bpr_submit_reel" value="Upload Reel">
</form>
<?php else: ?>
    <p>Please <a href="<?php echo wp_login_url(); ?>">login</a> to upload.</p>
<?php endif; ?>
