<form method="post" enctype="multipart/form-data">
    <?php wp_nonce_field('bpr_upload', 'bpr_upload_nonce'); ?>
    <p><label>Title:<br><input type="text" name="bpr_title" required></label></p>
    <p><label>Description:<br><textarea name="bpr_description" required></textarea></label></p>
    <p><label>Upload Video (MP4 only):<br><input type="file" name="bpr_video" accept="video/mp4" required></label></p>
    <p><button type="submit">Upload Reel</button></p>
</form>
