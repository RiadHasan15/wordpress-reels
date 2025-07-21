<?php if (is_user_logged_in()) : ?>
<form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
    <input type="hidden" name="action" value="bpr_upload_reel">
    <p><input type="text" name="bpr_title" placeholder="Video Title" required></p>
    <p><textarea name="bpr_description" placeholder="Description"></textarea></p>
    <p><input type="file" name="bpr_video" accept="video/mp4" required></p>
    <p><button type="submit">Upload Reel</button></p>
</form>
<?php else: ?>
<p>You must be logged in to upload a reel.</p>
<?php endif; ?>
